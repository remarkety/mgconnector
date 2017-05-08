<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use \Magento\Framework\Registry;
use \Magento\Framework\ObjectManager\ObjectManager;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Observer\EventMethods;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CustomerSerializer;
use Remarkety\Mgconnector\Serializer\OrderSerializer;

class TriggerCustomerAddressUpdateObserver extends EventMethods implements ObserverInterface
{
    /**
     * Customer update address
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
            $address = $observer->getEvent()->getCustomerAddress();
            /**
             * @var $customer \Magento\Customer\Api\Data\CustomerInterface
             */

            $customerId = $address->getCustomer()->getId();
            $this->customerRegistry->remove($customerId);
            $customer = $this->customerRepository->getById($customerId);

            if($this->_coreRegistry->registry('remarkety_customer_save_observer_executed_'.$customer->getId())) {
                return $this;
            }
            $this->_coreRegistry->register('remarkety_customer_save_observer_executed_'.$customer->getId(),true);

            $isDefaultBilling = false;
            if($customer && $customer->getDefaultBilling() == $address->getId()){
                $isDefaultBilling = true;
            }
            if (!$isDefaultBilling || !$customer->getId()) {
                return $this;
            }

            $this->_customerUpdate($customer);
        } catch (\Exception $ex){
            $this->logError($ex);
        }
        return $this;
    }
}
