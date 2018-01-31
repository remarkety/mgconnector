<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Customer\Model\Backend\Customer;
use Magento\Framework\Event\ObserverInterface;

class TriggerCustomerDeleteObserver extends EventMethods implements ObserverInterface {

    public function execute(\Magento\Framework\Event\Observer $observer){
        try {
            /**
             * @var $customer Customer
             */
            $customer = $observer->getEvent()->getCustomer();
            if (!$customer->getId()) {
                return $this;
            }
            $store = $customer->getStore();

            if($this->isWebhooksEnabled($store)) {
                $this->makeRequest(self::EVENT_CUSTOMERS_DELETED, array(
                    'id' => (int)$customer->getId(),
                    'email' => $customer->getEmail(),
                ), $store->getId());
            }

        } catch (\Exception $ex){
            $this->logError($ex);
        }
        return $this;
    }
}
