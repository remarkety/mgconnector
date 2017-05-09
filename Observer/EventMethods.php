<?php
namespace Remarkety\Mgconnector\Observer;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Request\Http;
use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use \Magento\Framework\UrlInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Remarkety\Mgconnector\Model\QueueRepository;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CustomerSerializer;
use Remarkety\Mgconnector\Serializer\OrderSerializer;
use Remarkety\Mgconnector\Serializer\ProductSerializer;
use Psr\Log\LoggerInterface;

class EventMethods {

    const REMARKETY_EVENTS_ENDPOINT = 'https://webhooks.remarkety.com/webhooks';
    const REMARKETY_METHOD = 'POST';
    const REMARKETY_TIMEOUT = 2;
    const REMARKETY_VERSION = 0.9;
    const REMARKETY_PLATFORM = 'MAGENTO_2';

    const EVENT_ORDERS_CREATED = 'orders/create';
    const EVENT_ORDERS_UPDATED = 'orders/updated';
    const EVENT_ORDERS_DELETE = 'orders/delete';
    const EVENT_PRODUCTS_CREATED = 'products/created';
    const EVENT_PRODUCTS_UPDATED = 'products/updated';
    const EVENT_PRODUCTS_DELETE = 'products/delete';
    const EVENT_CUSTOMERS_CREATE = 'customers/create';
    const EVENT_CUSTOMERS_UPDATED = 'customers/updated';
    const EVENT_CUSTOMERS_DELETED = 'customers/deleted';

    protected $_token = null;
    protected $_intervals = null;
    protected $_hasDataChanged = false;

    protected $_subscriber = null;
    protected $_origSubsciberData = null;

    protected $_address = null;
    protected $_origAddressData = null;

    protected $_coreRegistry;
    protected $_customerGroup;
    protected $_remarketyQueueRepo;
    protected $_store;
    protected $customerRepository;

    protected $orderSerializer;
    protected $customerSerializer;
    protected $addressSerializer;
    protected $productSerializer;

    protected $configHelper;
    protected $queueFactory;
    protected $request;
    protected $customerRegistry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        LoggerInterface $logger,
        Registry $coreRegistry,
        Subscriber $subscriber,
        Group $customerGroupModel,
        QueueRepository $remarketyQueueRepo,
        \Remarkety\Mgconnector\Model\QueueFactory $queueFactory,
        Store $store,
        ScopeConfigInterface $scopeConfig,
        OrderSerializer $orderSerializer,
        CustomerSerializer $customerSerializer,
        AddressSerializer $addressSerializer,
        ConfigHelper $configHelper,
        ProductSerializer $productSerializer,
        Http $request,
        CustomerRepository $customerRepository,
        CustomerRegistry $customerRegistry
        ){
        $this->customerRegistry = $customerRegistry;
        $this->customerRepository = $customerRepository;
        $this->request = $request;
        $this->logger = $logger;
        $this->customerSerializer = $customerSerializer;
        $this->orderSerializer = $orderSerializer;
        $this->addressSerializer = $addressSerializer;
        $this->productSerializer = $productSerializer;

        $this->_coreRegistry = $coreRegistry;
        $this->subscriber = $subscriber;
        $this->_customerGroup = $customerGroupModel;
        $this->_remarketyQueueRepo = $remarketyQueueRepo;
        $this->_store = $store;
        $this->scopeConfigInterface = $scopeConfig;

        $this->configHelper = $configHelper;
        $this->queueFactory = $queueFactory;

        $this->_token = $this->scopeConfigInterface->getValue('remarkety/mgconnector/api_key');
        $intervals = $this->scopeConfigInterface->getValue('remarkety/mgconnector/intervals');
        if(empty($intervals)){
            $this->_intervals = [1,3,10];
        } else {
            $this->_intervals = explode(',', $intervals);
        }
    }

    protected function isWebhooksEnabled($store){
        if(!$this->configHelper->isWebhooksGloballyEnabled()){
            return false;
        }
        return !empty($this->configHelper->getRemarketyPublicId($store));
    }

    protected function shouldSendProductUpdates(){
        return $this->configHelper->shouldSendProductUpdates();
    }

    protected function _customerUpdate(\Magento\Customer\Api\Data\CustomerInterface $customer, $isNew = false)
    {
        if($this->isWebhooksEnabled($customer->getStoreId())) {
            $eventType = self::EVENT_CUSTOMERS_UPDATED;
            if($isNew){
                $eventType = self::EVENT_CUSTOMERS_CREATE;
            }
            $data = $this->customerSerializer->serialize($customer);

            $this->makeRequest($eventType, $data, $customer->getStoreId());
        }
        return $this;
    }

    protected function _getRequestConfig($eventType)
    {
        return array(
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(
                CURLOPT_HEADER => true,
                CURLOPT_CONNECTTIMEOUT => self::REMARKETY_TIMEOUT
            ),
        );
    }

    protected function _getHeaders($eventType,$payload, $storeId = null)
    {
        $domain = $this->_store->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $domain = substr($domain, 7, -1);

        if(empty($storeId) && isset($payload['storeId'])){
            $storeId = $payload['storeId'];
        }

        $headers = [
            'X-Domain: ' . $domain,
            'X-Token: ' . $this->_token,
            'X-Event-Type: ' . $eventType,
            'X-Platform: ' . self::REMARKETY_PLATFORM,
            'X-Version: ' . self::REMARKETY_VERSION,
            'X-Magento-Store-Id: ' . (empty($storeId) ? $this->_store->getId() : $storeId)
        ];
        return $headers;
    }

    protected function shouldSendEvent($eventType, $payload, $storeId){
        $data = array(
            'eventType' => $eventType,
            'payload' => $payload,
            'storeId' => $storeId
        );
        $hash = md5(serialize($data));
        if($this->_coreRegistry->registry($hash)){
            return false;
        }
        $this->_coreRegistry->register($hash, 1);
        return true;
    }

    public function makeRequest($eventType, $payload, $storeId = null, $attempt = 0, $queueId = null)
    {
        try {
            if(!$this->shouldSendEvent($eventType, $payload, $storeId)){
                //safety for not sending the same event on same event
                $this->logger->debug('Event already sent ' . $eventType);
                return true;
            }

            $url = self::REMARKETY_EVENTS_ENDPOINT;
            if(!empty($storeId)){
                $remarketyId = $this->configHelper->getRemarketyPublicId($storeId);
                $url .= '?debug=1&storeId=' . $remarketyId;
            }
            $client = new \Zend_Http_Client($url, $this->_getRequestConfig($eventType));
            $payload = array_merge($payload, $this->_getPayloadBase($eventType));
            $json = json_encode($payload);

            $response = $client
                ->setHeaders($this->_getHeaders($eventType, $payload, $storeId))
                ->setRawData($json, 'application/json')
                ->request(self::REMARKETY_METHOD);

            switch ($response->getStatus()) {
                case '200':
                    return true;
                case '400':
                    throw new \Exception('Request has been malformed.');
                case '401':
                    throw new \Exception('Request failed, probably wrong API key or inactive account.');
                default:
                    $err = $response->getStatus() . ' - ' . $response->getRawBody();
                    $this->_queueRequest($eventType, $payload, $attempt+1, $queueId, $storeId, $err);
            }
        } catch(\Exception $e) {
            $err = $e->getCode() . ' - ' . $e->getMessage();
            $this->_queueRequest($eventType, $payload, $attempt+1, $queueId, $storeId, $err);
        }

        return false;
    }

    protected function _queueRequest($eventType, $payload, $attempt, $queueId, $storeId, $err = null)
    {

        $queueModel = null;
        if(!empty($this->_intervals[$attempt-1])) {
            $now = time();
            $nextAttempt = $now + (int)$this->_intervals[$attempt-1] * 60;
            if($queueId) {
                $queueModel = $this->_remarketyQueueRepo->getById($queueId);
                $queueModel->setAttempts($attempt);
                $queueModel->setLastAttempt( date("Y-m-d H:i:s", $now) );
                $queueModel->setNextAttempt( date("Y-m-d H:i:s", $nextAttempt) );
                $queueModel->setStoreId($storeId);
                if(!empty($err)){
                    $queueModel->setLastErrorMessage($err);
                }
            } else {
                $queueModel = $this->queueFactory->create();
                $this->_remarketyQueueRepo->save($queueModel);
                $queueModel->setData(array(
                    'event_type' => $eventType,
                    'payload' => json_encode($payload),
                    'attempts' => $attempt,
                    'last_attempt' => date("Y-m-d H:i:s", $now),
                    'next_attempt' => date("Y-m-d H:i:s", $nextAttempt),
                    'status' => 1,
                    'store_id' => $storeId
                ));
                if(!empty($err)){
                    $queueModel->setLastErrorMessage($err);
                }
            }
            return $this->_remarketyQueueRepo->save($queueModel);
        } elseif($queueId) {
            $queueModel = $this->_remarketyQueueRepo->getById($queueId);
            $queueModel->setAttempts($attempt);
            $queueModel->setStatus(0);
            return $this->_remarketyQueueRepo->save($queueModel);
        }
        return false;
    }

    protected function _getPayloadBase($eventType)
    {
        date_default_timezone_set('UTC');
        $arr = array(
            'timestamp' => (string)time(),
            'event_id' => $eventType,
        );
        return $arr;
    }


    protected function _prepareCustomerSubscribtionUpdateData(Subscriber $subscriber, $clientIp = null)
    {
        $arr = array(
            'email' => $subscriber->getSubscriberEmail(),
            'accepts_marketing' => $subscriber->getSubscriberStatus() == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED,
            'storeId' => $subscriber->getStoreId()
        );

        if(!empty($clientIp)){
            $arr['client_ip'] = $clientIp;
        }

        return $arr;
    }

    protected function _prepareCustomerSubscribtionDeleteData(Subscriber $subscriber)
    {
        $arr = array(
            'email' => $subscriber->getSubscriberEmail(),
            'accepts_marketing' => false,
            'storeId' => $subscriber->getStoreId()
        );

        return $arr;
    }

    public function logError(\Exception $exception){
        $this->logger->error("Remarkety:".self::class." - " . $exception->getMessage(), [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
