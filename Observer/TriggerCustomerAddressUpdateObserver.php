<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;

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
