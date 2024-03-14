<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Model\QueueRepository;
use Remarkety\Mgconnector\Serializer;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CustomerSerializer;
use Remarkety\Mgconnector\Serializer\OrderSerializer;
use Remarkety\Mgconnector\Serializer\ProductSerializer;

class TriggerOrderPlacedFinished extends EventMethods implements ObserverInterface
{
    protected $subscriberFactory;

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
        CustomerRegistry $customerRegistry,
        StoreManager $storeManager,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {
        parent::__construct(
            $logger,
            $coreRegistry,
            $subscriber,
            $customerGroupModel,
            $remarketyQueueRepo,
            $queueFactory,
            $store,
            $scopeConfig,
            $orderSerializer,
            $customerSerializer,
            $addressSerializer,
            $configHelper,
            $productSerializer,
            $request,
            $customerRepository,
            $customerRegistry,
            $storeManager,
            $serialize);
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->startTiming(self::class);
            /**
             * @var $order Order
             */
            $order = $observer->getEvent()->getDataByKey('order');

            $rm_email_consent = null;
            $rm_sms_consent = null;
            $extensionAttributes = null;
            $orderPlacedParams = json_decode($this->request->getContent(), true);
            if (isset($orderPlacedParams['billingAddress']['extension_attributes'])) {
                $extensionAttributes = $orderPlacedParams['billingAddress']['extension_attributes'];
            } else if (isset($orderPlacedParams['billingAddress']['extensionAttributes'])) {
                $extensionAttributes = $orderPlacedParams['billingAddress']['extensionAttributes'];
            }

            if ($extensionAttributes) {
                if (isset($extensionAttributes['rm_email_consent']) && $extensionAttributes['rm_email_consent']) {
                    $rm_email_consent = true;
                }

                if (isset ($extensionAttributes['rm_sms_consent']) && $extensionAttributes['rm_sms_consent']) {
                    $rm_sms_consent = true;
                }
            }

            if (!empty($rm_email_consent) || !empty($rm_sms_consent)) {
                $email = $order->getCustomerEmail();
                $customer = null;
                $subscriber = $this->subscriberFactory->create()->loadByEmail($email);
                try {
                    $customer = $this->customerRepository->get($email); // get by email and not by id since if the customer is not logged in we will not find him
                } catch (\Exception $e) {}
                if (!empty($rm_email_consent) && $subscriber) {
                    if ($customer) {
                        $subscriber->setCustomerId($customer->getId());
                    }
                    $subscriber->setStoreId($order->getStoreId());
                    $subscriber->setEmail($email);
                    $subscriber->setStatus(\Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED);
                    $subscriber->save();
                }
                if (!empty($rm_sms_consent)) {
                    if ($customer) {
                        $customer->setCustomAttribute('rm_sms_consent', $rm_sms_consent);
                        $this->customerRepository->save($customer);
                    }
                }
            }
            $this->endTiming(self::class);
        } catch (\Exception $ex) {
            $this->logError($ex);
        }
    }
}
