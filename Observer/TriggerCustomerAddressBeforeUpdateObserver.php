<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use \Magento\Framework\ObjectManager\ObjectManager;
use \Magento\Framework\Registry;
use Remarkety\Mgconnector\Observer\EventMethods;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;


class TriggerCustomerAddressBeforeUpdateObserver extends EventMethods implements ObserverInterface
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

    protected $__objectManager;

    public function __construct(
        CustomerFactory $customerFactory,
        ObjectManager $objectManager,
        Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        Subscriber $subscriber,
        Group $customerGroupModel,
        Queue $remarketyQueue,
        Store $store,
        ScopeConfigInterface $scopeConfig

    ) {
        parent::__construct($registry, $subscriber, $customerGroupModel, $remarketyQueue, $store, $scopeConfig);
        $this->_customerFactory = $customerFactory;
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->session = $customerSession;
    }
    /**
     * Apply catalog price rules to product in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $address = $this->session->getCustomer()->getDefaultBillingAddress();
        if(!empty($address)) {
            $this->_origAddressData = $address->getData();
        }
        $this->_coreRegistry->unregister( 'customer_orig_address' );
        $this->_coreRegistry->register('customer_orig_address', $this->_origAddressData);
        return $this;
    }
}
