<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Customer\Model\Backend\Customer;
use Magento\Framework\Event\ObserverInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Observer\EventMethods;
use \Magento\Customer\Model\Session;
use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CustomerSerializer;
use Remarkety\Mgconnector\Serializer\OrderSerializer;

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
