<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use Remarkety\Mgconnector\Helper\ConfigHelper;

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
            if ($this->ignoreCustomerUpdate()) {
                return $this;
            }
            $address = $observer->getEvent()->getCustomerAddress();
            /**
             * @var $customer \Magento\Customer\Api\Data\CustomerInterface
             */

            $customerId = $address->getCustomer()->getId();
            $this->customerRegistry->remove($customerId);
            $customer = $this->customerRepository->getById($customerId);

            $toUse = $this->configHelper->getCustomerAddressType();
            $isDefaultAddress = false;
            if ($toUse === ConfigHelper::CUSTOMER_ADDRESS_BILLING && $address->getIsDefaultBilling()) {
                $isDefaultAddress = true;
            } elseif ($toUse === ConfigHelper::CUSTOMER_ADDRESS_SHIPPING && $address->getIsDefaultShipping()) {
                $isDefaultAddress = true;
            }
            if (!$isDefaultAddress || !$customer->getId()) {
                return $this;
            }
            if ($this->_coreRegistry->registry('remarkety_customer_address_updated_'.$customer->getId())) {
                return $this;
            }

            $this->_coreRegistry->register('remarkety_customer_address_updated_'.$customer->getId(), true);

            $this->_customerUpdate($customer);
            $this->endTiming(self::class);
        } catch (\Exception $ex) {
            $this->logError($ex);
        }
        return $this;
    }
}
