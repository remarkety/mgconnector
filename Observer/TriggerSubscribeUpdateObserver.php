<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ObserverInterface;
use \Magento\Customer\Model\Session;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use Magento\Quote\Api\CartRepositoryInterface;
use Remarkety\Mgconnector\Helper\DataOverride;
use Remarkety\Mgconnector\Model\QueueRepository;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use \Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Checkout\Model\Session as CheckoutSession;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CustomerSerializer;
use Remarkety\Mgconnector\Serializer\OrderSerializer;
use Remarkety\Mgconnector\Serializer\ProductSerializer;
use Psr\Log\LoggerInterface;

class TriggerSubscribeUpdateObserver extends EventMethods implements ObserverInterface {

    protected $_subscriber = null;
    protected $_checkoutSession;
    protected $cartRepository;
    protected $session;
    protected $remoteAddress;
    private $dataOverride;
    public function __construct(
        LoggerInterface $logger,
        Session $customerSession,
        CheckoutSession $CheckoutSession,
        Registry $registry,
        Subscriber $subscriber,
        Group $customerGroupModel,
        QueueRepository $remarketyQueueRepo,
        Store $store,
        ScopeConfigInterface $scopeConfig,
        StoreManager $sManager,
        OrderSerializer $orderSerializer,
        CustomerSerializer $customerSerializer,
        AddressSerializer $addressSerializer,
        ConfigHelper $configHelper,
        ProductSerializer $productSerializer,
        CartRepositoryInterface $cartRepository,
        Http $request,
        CustomerRepository $customerRepository,
        \Remarkety\Mgconnector\Model\QueueFactory $queueFactory,
        CustomerRegistry $customerRegistry,
        RemoteAddress $remoteAddress,
        StoreManager $storeManager,
        DataOverride $dataOverride
    ){
        parent::__construct($logger, $registry, $subscriber, $customerGroupModel, $remarketyQueueRepo, $queueFactory, $store, $scopeConfig, $orderSerializer, $customerSerializer, $addressSerializer, $configHelper, $productSerializer, $request, $customerRepository, $customerRegistry, $storeManager);
        $this->session = $customerSession;
        $this->_checkoutSession = $CheckoutSession;
        $this->_store = $sManager->getStore();
        $this->cartRepository = $cartRepository;
        $this->remoteAddress = $remoteAddress;
        $this->dataOverride = $dataOverride;
    }
    /**
     * Apply catalog price rules to product in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer){
        try {
            $this->startTiming(self::class);
            /**
             * @var $subscriber Subscriber
             */
            $subscriber = $observer->getEvent()->getSubscriber();

            if(!$subscriber->isStatusChanged()) {
                return $this;
            }

            if(!$this->_coreRegistry->registry('subscriber_object_data_observer'))
                $this->_coreRegistry->register('subscriber_object_data_observer', 1);

            if($subscriber->getId()) {

                if(!$this->isWebhooksEnabledSpecificStore($subscriber->getStoreId())){
                    return $this;
                }

                if ($this->_coreRegistry->registry('remarkety_subscriber_deleted_' . $subscriber->getSubscriberEmail()))
                    return $this;
                if ($this->_coreRegistry->registry('remarkety_subscriber_updated_' . $subscriber->getSubscriberEmail()))
                    return $this;

                $status = $subscriber->getStatus();
                $eventType = 'newsletter/subscribed';
                switch($status){
                    case Subscriber::STATUS_SUBSCRIBED:
                        $eventType = 'newsletter/subscribed';
                        break;
                    case Subscriber::STATUS_UNSUBSCRIBED:
                        $eventType = 'newsletter/unsubscribed';
                        break;
                    case Subscriber::STATUS_UNCONFIRMED:
                    case Subscriber::STATUS_NOT_ACTIVE:
                        return $this;
                }
                if($eventType == 'newsletter/unsubscribed') {
                    if ($this->request->getFullActionName() === 'customer_account_createpost') {
                        //workaround to prevent "unsubscribe" event when email verification is required
                        //for new account and email is already approved
                        return $this;
                    }
                }

                $data = $this->_prepareCustomerSubscribtionUpdateData($subscriber, $this->remoteAddress->getRemoteAddress());
                $data = $this->dataOverride->newsletter($data);
                $this->makeRequest($eventType, $data, $subscriber->getStoreId(), 0, null, $this->_forceSyncCustomersWebhooks);

                if($this->_store->getId() != 0){
                    $email = $subscriber->getSubscriberEmail();
                    if(!empty($email)){
                        //for webtracking use
                        $this->session->setSubscriberEmail($email);
                        //add email to cart
                        $cart = $this->_checkoutSession->getQuote();
                        if($cart && !is_null($cart->getId()) && is_null($cart->getCustomerEmail())){
                            $cart->setCustomerEmail($email);
                            $this->cartRepository->save($cart);
                        }
                    }
                }
                $this->_coreRegistry->register('remarkety_subscriber_updated_' . $subscriber->getSubscriberEmail(), 1, true);
                $this->endTiming(self::class);
            }
        } catch (\Exception $ex){
            $this->logError($ex);
        }
        return $this;
    }
}
