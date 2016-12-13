<?php
namespace Remarkety\Mgconnector\Observer;

use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use \Magento\Framework\UrlInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;


class EventMethods {

    const REMARKETY_EVENTS_ENDPOINT = 'https://api-events.remarkety.com/v1';
    const REMARKETY_METHOD = 'POST';
    const REMARKETY_TIMEOUT = 2;
    const REMARKETY_VERSION = 0.9;
    const REMARKETY_PLATFORM = 'MAGENTO';

    protected $_token = null;
    protected $_intervals = null;
    protected $_hasDataChanged = false;

    protected $_subscriber = null;
    protected $_origSubsciberData = null;

    protected $_address = null;
    protected $_origAddressData = null;

    protected $_coreRegistry;
    protected $_customerGroup;
    protected $_remarketyQueue;
    protected $_store;

    public function __construct(
        Registry $coreRegistry,
        Subscriber $subscriber,
        Group $customerGroupModel,
        Queue $remarketyQueue,
        Store $store,
        ScopeConfigInterface $scopeConfig

){
        $this->_coreRegistry = $coreRegistry;
        $this->subscriber = $subscriber;
        $this->_customerGroup = $customerGroupModel;
        $this->_remarketyQueue = $remarketyQueue;
        $this->_store = $store;
        $this->scopeConfigInterface = $scopeConfig;

        $this->_token = $this->scopeConfigInterface->getValue('remarkety/mgconnector/api_key');
        $intervals = $this->scopeConfigInterface->getValue('remarkety/mgconnector/intervals');
        $this->_intervals = explode(',', $intervals);
    }

    protected function _customerRegistration()
    {
        $this->makeRequest('customers/create', $this->_prepareCustomerUpdateData());
        return $this;
    }

    protected function _customerUpdate()
    {
        if($this->_hasDataChanged()) {
            $this->makeRequest('customers/update', $this->_prepareCustomerUpdateData());
        }
        return $this;
    }

    protected function _hasDataChanged()
    {
        $customerReg = $this->_coreRegistry->registry('customer_data_object_observer');

        if(!$this->_hasDataChanged && $customerReg) {
            $validate = array(
                'firstname',
                'lastname',
                'title',
                'birthday',
                'gender',
                'email',
                'group_id',
                'default_billing',
                'is_subscribed',
            );
            $originalData = $customerReg->getOrigData();
            $currentData = $customerReg->getData();
            foreach ($validate as $field) {
                if (isset($originalData[$field])) {
                    if (!isset($currentData[$field]) || $currentData[$field] != $originalData[$field]) {
                        $this->_hasDataChanged = true;
                        break;
                    }
                }
            }
            $customerData = $customerReg->getData();
            if(!$this->_hasDataChanged && isset($customerData['is_subscribed'])) {
                $subscriber = $this->subscriber->loadByEmail($customerReg->getEmail());
                $isSubscribed = $subscriber->getId() ? $subscriber->getData('subscriber_status') == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED : false;

                if($customerData['is_subscribed'] !== $isSubscribed) {
                    $this->_hasDataChanged = true;
                }
            }
        }
        if(!$this->_hasDataChanged && $this->_coreRegistry->registry('customer_address_object_observer') && $this->_coreRegistry->registry('customer_orig_address')) {
            $validate = array(
                'street',
                'city',
                'region',
                'postcode',
                'country_id',
                'telephone',
            );

            $addressObs = $this->_coreRegistry->registry('customer_address_object_observer')->getData();
            $originalAddressObs = $this->_coreRegistry->registry('customer_orig_address');
            $addressDiffKeys = array_keys( array_diff($addressObs, $originalAddressObs));

            if(array_intersect($addressDiffKeys, $validate)) {
                $this->_hasDataChanged = true;
            }
        }

        return $this->_hasDataChanged;
    }

    protected function _getRequestConfig($eventType)
    {
        return array(
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => array(
//                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER => true,
                CURLOPT_CONNECTTIMEOUT => self::REMARKETY_TIMEOUT
//	            CURLOPT_SSL_CIPHER_LIST => "RC4-SHA"
            ),
        );
    }

    protected function _getHeaders($eventType,$payload)
    {
        $domain = $this->_store->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $domain = substr($domain, 7, -1);

        $headers = array(
            'X-Domain: ' . $domain,
            'X-Token: ' . $this->_token,
            'X-Event-Type: ' . $eventType,
            'X-Platform: ' . self::REMARKETY_PLATFORM,
            'X-Version: ' . self::REMARKETY_VERSION,
            'X-Magento-Store-Id: ' . $this->_store->getId()
        );
        if (isset($payload['storeId']))
            $headers[] = 'X-Magento-Store-Id: ' . $payload['storeId'];
        return $headers;
    }

    public function makeRequest($eventType, $payload, $attempt = 1, $queueId = null)
    {
        try {
            $client = new \Zend_Http_Client(self::REMARKETY_EVENTS_ENDPOINT, $this->_getRequestConfig($eventType));
            $payload = array_merge($payload, $this->_getPayloadBase($eventType));
            $json = json_encode($payload);

            $response = $client
                ->setHeaders($this->_getHeaders($eventType, $payload))
                ->setRawData($json, 'application/json')
                ->request(self::REMARKETY_METHOD);

//            Mage::log("Sent event to endpoint: ".$json."; Response (".$response->getStatus()."): ".$response->getBody(), \Zend_Log::DEBUG, REMARKETY_LOG);
            switch ($response->getStatus()) {
                case '200':
                    return true;
                case '400':
                    throw new \Exception('Request has been malformed.');
                case '401':
                    throw new \Exception('Request failed, probably wrong API key or inactive account.');
                default:
                    $this->_queueRequest($eventType, $payload, $attempt, $queueId);
            }
        } catch(\Exception $e) {
            $this->_queueRequest($eventType, $payload, $attempt, $queueId);
        }

        return false;
    }

    protected function _queueRequest($eventType, $payload, $attempt, $queueId)
    {
        $queueModel = $this->_remarketyQueue;

        if(!empty($this->_intervals[$attempt-1])) {
            $now = time();
            $nextAttempt = $now + (int)$this->_intervals[$attempt-1] * 60;
            if($queueId) {
                $queueModel->load($queueId);
                $queueModel->setAttempts($attempt);
                $queueModel->setLastAttempt( date("Y-m-d H:i:s", $now) );
                $queueModel->setNextAttempt( date("Y-m-d H:i:s", $nextAttempt) );
            } else {
                $queueModel->setData(array(
                    'event_type' => $eventType,
                    'payload' => serialize($payload),
                    'attempts' => $attempt,
                    'last_attempt' => date("Y-m-d H:i:s", $now),
                    'next_attempt' => date("Y-m-d H:i:s", $nextAttempt),
                ));
            }
            return $queueModel->save();
        } elseif($queueId) {
            $queueModel->load($queueId);
            $queueModel->setStatus(0);
            return $queueModel->save();
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

    protected function _prepareCustomerUpdateData()
    {
        $customerReg = $this->_coreRegistry->registry('customer_data_object_observer');
        if($customerReg) {
            $arr = array(
                'id' => (int)$customerReg->getId(),
                'email' => $customerReg->getEmail(),
                'created_at' => date('c', strtotime($customerReg->getCreatedAt())),
                'first_name' => $customerReg->getFirstname(),
                'last_name' => $customerReg->getLastname(),
                'store_id' => $customerReg->getStoreId(),
                //'extra_info' => array(),
            );

            $isSubscribed = $customerReg->getIsSubscribed();
            if ($isSubscribed === null) {
                $subscriber = $this->subscriber->loadByEmail($customerReg->getEmail());
                if ($subscriber->getId()) {
                    $isSubscribed = $subscriber->getData('subscriber_status') == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED;
                } else {
                    $isSubscribed = false;
                }
            }
            $arr = array_merge($arr, array('accepts_marketing' => (bool)$isSubscribed));

            if ($title = $customerReg->getPrefix()) {
                $arr = array_merge($arr, array('title' => $title));
            }

            if ($dob = $customerReg->getDob()) {
                $arr = array_merge($arr, array('birthdate' => $dob));
            }

            if ($gender = $customerReg->getGender()) {
                $arr = array_merge($arr, array('gender' => $gender));
            }

            if ($address = $customerReg->getDefaultBillingAddress()) {
                $street = $address->getStreet();
                $arr = array_merge($arr, array('default_address' => array(
                    'address1' => isset($street[0]) ? $street[0] : '',
                    'address2' => isset($street[1]) ? $street[1] : '',
                    'city' => $address->getCity(),
                    'province' => $address->getRegion(),
                    'phone' => $address->getTelephone(),
                    'country_code' => $address->getCountryId(),
                    'zip' => $address->getPostcode(),
                )));
            }


            if ($group = $this->_customerGroup->load($customerReg->getGroupId())) {
                $arr = array_merge($arr, array('groups' => array(
                    array(
                        'id' => (int)$customerReg->getGroupId(),
                        'name' => $group->getCustomerGroupCode(),
                    )
                )));
            }

            return $arr;
        }
        return array();
    }


    protected function _prepareCustomerSubscribtionUpdateData()
    {
        $subscriber = $this->_coreRegistry->registry('subscriber_object_data_observer');
        $arr = array(
            'email' => $subscriber->getSubscriberEmail(),
            'accepts_marketing' => $subscriber->getData('subscriber_status') == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED,
            'storeId' => $this->_store->getId()
        );

        return $arr;
    }

    protected function _prepareCustomerSubscribtionDeleteData()
    {
        $subscriber = $this->_coreRegistry->registry('subscriber_object_data_observer');
        $arr = array(
            'email' => $subscriber->getSubscriberEmail(),
            'accepts_marketing' => false,
            'storeId' => $this->_store->getId()
        );

        return $arr;
    }

    public function resend($queueItems,$resetAttempts = false) {
        $sent=0;
        foreach($queueItems as $_queue) {
            $result = $this->makeRequest($_queue->getEventType(), unserialize($_queue->getPayload()), $resetAttempts ? 1 : ($_queue->getAttempts()+1), $_queue->getId());
            if($result) {
                $this->_remarketyQueue
                    ->load($_queue->getId())
                    ->delete();
                $sent++;
            }
        }
        return $sent;
    }

    public function run()
    {
        $collection = $this->_remarketyQueue->getCollection();
        $nextAttempt = date("Y-m-d H:i:s");
        $collection
            ->getSelect()
            ->where('next_attempt <= ?', $nextAttempt)
            ->where('status = 1')
            ->order('main_table.next_attempt asc');
        $this->resend($collection);
        return $this;
    }

}
