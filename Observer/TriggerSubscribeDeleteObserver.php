<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;

class TriggerSubscribeDeleteObserver extends EventMethods implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $subscriber = $observer->getEvent()->getSubscriber();
            $regKey = 'remarkety_subscriber_deleted_' . $subscriber->getEmail();
            if (!$this->_coreRegistry->registry($regKey) && $subscriber->getId()) {
                $this->makeRequest(
                    'newsletter/unsubscribed',
                    $this->_prepareCustomerSubscribtionDeleteData($subscriber),
                    $subscriber->getStoreId()
                );
                $this->_coreRegistry->register($regKey, 1, true);
            }
        } catch (\Exception $ex){
            $this->logError($ex);
        }
        return $this;
    }
}
