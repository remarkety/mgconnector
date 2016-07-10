<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use \Magento\Framework\Registry;
use \Magento\Framework\ObjectManager\ObjectManager;
use Remarkety\Mgconnector\Observer\EventMethods;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class TriggerCustomerAddressUpdateObserver extends EventMethods implements ObserverInterface
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

    protected $_address = null;

    protected $_customer = null;

    public function __construct(
        CustomerFactory $customerFactory,
        ObjectManager $objectManager,
        Registry $registry,
        Subscriber $subscriber,
        Group $customerGroupModel,
        Queue $remarketyQueue,
        Store $store,
        ScopeConfigInterface $scopeConfig

    ) {
        parent::__construct($registry, $subscriber, $customerGroupModel, $remarketyQueue, $store, $scopeConfig);
        $this->_customerFactory = $customerFactory;
        $this->_objectManager = $objectManager;
    }

    /**
     * Apply catalog price rules to product in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_address = $observer->getEvent()->getCustomerAddress();
        $this->_customer = $this->_address->getCustomer();

        if($this->_coreRegistry->registry('remarkety_customer_save_observer_executed_'.$this->_customer->getId())) {
            return $this;
        }

        $this->_store = $this->_customer->getStore();

        $isDefaultBilling =
            ($this->_customer == null || $this->_customer->getDefaultBillingAddress() == null)
                ? false
                : ($this->_address->getId() == $this->_customer->getDefaultBillingAddress()->getId());
        if (!$isDefaultBilling || !$this->_customer->getId()) {
            return $this;
        }
        $this->_coreRegistry->register('customer_address_object_observer', $this->_address);
        $this->_coreRegistry->register('customer_data_object_observer', $this->_customer);
        $this->_customerUpdate();

        $this->_coreRegistry->register('remarkety_customer_save_observer_executed_'.$this->_customer->getId(),true);
        return $this;
    }
}