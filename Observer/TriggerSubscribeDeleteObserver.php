<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Customer\Model\Session;
use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class TriggerSubscribeDeleteObserver extends EventMethods implements ObserverInterface
{


    public function __construct(
        Session $customerSession,
        Registry $registry,
        Subscriber $subscriber,
        Group $customerGroupModel,
        Queue $remarketyQueue,
        Store $store,
        ScopeConfigInterface $scopeConfig,
        StoreManager $sManager
    ){
        parent::__construct($registry, $subscriber, $customerGroupModel, $remarketyQueue, $store, $scopeConfig);
        $this->session = $customerSession;
        $this->_store = $sManager->getStore();
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $subscriber = $observer->getEvent()->getSubscriber();
        if (!$this->_coreRegistry->registry('remarkety_subscriber_deleted_' . $subscriber->getEmail()) && $subscriber->getId()) {
            $this->makeRequest('customers/update', $this->_prepareCustomerSubscribtionDeleteData());
        }

        return $this;
    }
}