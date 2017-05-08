<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Customer\Model\Session;
use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CustomerSerializer;
use Remarkety\Mgconnector\Serializer\OrderSerializer;

class TriggerSubscribeDeleteObserver extends EventMethods implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $subscriber = $observer->getEvent()->getSubscriber();
            if (!$this->_coreRegistry->registry('remarkety_subscriber_deleted_' . $subscriber->getEmail()) && $subscriber->getId()) {
                $this->makeRequest(
                    'newsletter/unsubscribed',
                    $this->_prepareCustomerSubscribtionDeleteData($subscriber),
                    $subscriber->getStoreId()
                );
            }
        } catch (\Exception $ex){
            $this->logError($ex);
        }
        return $this;
    }
}
