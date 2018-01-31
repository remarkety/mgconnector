<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\CustomerFactory;


class TriggerCustomerAddressBeforeUpdateObserver extends EventMethods implements ObserverInterface
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
        } catch (\Exception $ex){
            $this->logError($ex);
        }
        return $this;
    }
}
