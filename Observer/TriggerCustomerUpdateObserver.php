<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;


class TriggerCustomerUpdateObserver extends EventMethods implements ObserverInterface
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;


    protected $_origAddressData = null;
    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;
    protected $_customer = null;

    public function __construct(
        CustomerFactory $customerFactory,
        Registry $registry,
        Subscriber $subscriber,
        Group $customerGroupModel,
        Queue $remarketyQueue,
        Store $store,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($registry, $subscriber, $customerGroupModel, $remarketyQueue, $store, $scopeConfig);
        $this->_customerFactory = $customerFactory;
    }
    /**
     * Apply catalog price rules to product in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_customer = $observer->getEvent()->getCustomer();

        if($this->_coreRegistry->registry('remarkety_customer_save_observer_executed_'.$this->_customer->getId()) || !$this->_customer->getId()) {
            return $this;
        }

        $this->_coreRegistry->unregister( 'customer_data_object_observer' );
        $this->_coreRegistry->register('customer_data_object_observer', $this->_customer);
        $this->_store = $this->_customer->getStore();
        if($this->_customer->getOrigData() === null) {
            $this->_customerRegistration();
        } else {
            $this->_customerUpdate();
        }

        $this->_coreRegistry->unregister( 'remarkety_customer_save_observer_executed_'.$this->_customer->getId() );

        $this->_coreRegistry->register('remarkety_customer_save_observer_executed_'.$this->_customer->getId(),true);
        return $this;
    }

}
