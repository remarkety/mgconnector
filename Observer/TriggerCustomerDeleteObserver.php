<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Remarkety\Mgconnector\Observer\EventMethods;
use \Magento\Customer\Model\Session;
use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class TriggerCustomerDeleteObserver extends EventMethods implements ObserverInterface {


    public function __construct(
        Session $customerSession,
        Registry $registry,
        Subscriber $subscriber,
        Group $customerGroupModel,
        Queue $remarketyQueue,
        Store $store,
        ScopeConfigInterface $scopeConfig
    ){
        parent::__construct($registry, $subscriber, $customerGroupModel, $remarketyQueue, $store, $scopeConfig);
        $this->session = $customerSession;
        $this->_coreRegistry = $registry;
    }

    public function execute(\Magento\Framework\Event\Observer $observer){
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer->getId()) {
            return $this;
        }
        $this->_store = $customer->getStore();

        $this->makeRequest('customers/delete', array(
            'id' => (int)$customer->getId(),
            'email' => $customer->getEmail(),
        ));
        return $this;
    }
}