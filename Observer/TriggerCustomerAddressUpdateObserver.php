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
            $this->startTiming(self::class);
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

            if($this->_coreRegistry->registry('remarkety_customer_address_updated_'.$customer->getId())) {
                return $this;
            }

            $isDefaultAddress = $address->getIsDefaultShipping() || $address->getIsDefaultBilling();
            if (!$isDefaultAddress || !$customer->getId()) {
                return $this;
            }
            $this->_coreRegistry->register('remarkety_customer_address_updated_'.$customer->getId(),true);

            $this->_customerUpdate($customer);
            $this->endTiming(self::class);
        } catch (\Exception $ex){
            $this->logError($ex);
        }
        return $this;
    }
}
