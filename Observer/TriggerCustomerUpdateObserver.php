<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CustomerSerializer;
use Remarkety\Mgconnector\Serializer\OrderSerializer;


class TriggerCustomerUpdateObserver extends EventMethods implements ObserverInterface
{
    /**
     * Apply catalog price rules to product in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if($this->request->getFullActionName() == "customer_account_loginPost"){
                return $this;
            }
            /**
             * @var $customer \Magento\Customer\Api\Data\CustomerInterface
             */
            $customer = $this->customerRepository->getById($observer->getEvent()->getCustomer()->getId());
            if($this->_coreRegistry->registry('remarkety_customer_save_observer_executed_'.$customer->getId()) || !$customer->getId()) {
                return $this;
            }
            $this->_coreRegistry->register('remarkety_customer_save_observer_executed_'.$customer->getId(),true);

            $isNew = $customer->getCreatedAt() == $customer->getUpdatedAt();
            $this->_customerUpdate($customer, $isNew);
        } catch (\Exception $ex){
            $this->logError($ex);
        }
        return $this;
    }
}
