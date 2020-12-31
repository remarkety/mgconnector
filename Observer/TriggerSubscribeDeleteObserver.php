<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Helper\DataOverride;
use Remarkety\Mgconnector\Model\QueueRepository;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CustomerSerializer;
use Remarkety\Mgconnector\Serializer\OrderSerializer;
use Remarkety\Mgconnector\Serializer\ProductSerializer;

class TriggerSubscribeDeleteObserver extends EventMethods implements ObserverInterface
{
    private $dataOverride;
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
        DataOverride $dataOverride,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize
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
            $serialize
        );
        $this->dataOverride = $dataOverride;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->startTiming(self::class);
            $subscriber = $observer->getEvent()->getSubscriber();
            $regKey = 'remarkety_subscriber_deleted_' . $subscriber->getEmail();
            if (!$this->_coreRegistry->registry($regKey) && $subscriber->getId()) {
                if (!$this->isWebhooksEnabledSpecificStore($subscriber->getStoreId())) {
                    return $this;
                }

                $data = $this->_prepareCustomerSubscribtionDeleteData($subscriber);
                $data = $this->dataOverride->newsletter($data);
                $this->makeRequest(
                    'newsletter/unsubscribed',
                    $data,
                    $subscriber->getStoreId()
                );
                $this->_coreRegistry->register($regKey, 1, true);
            }
            $this->endTiming(self::class);
        } catch (\Exception $ex) {
            $this->logError($ex);
        }
        return $this;
    }
}
