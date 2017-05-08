<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 4/26/17
 * Time: 1:59 PM
 */

namespace Remarkety\Mgconnector\Observer;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Remarkety\Mgconnector\Serializer;

class TriggerOrderUpdated extends EventMethods implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            /**
             * @var $order Order
             */
            $order = $event->getDataByKey('order');
            if ($order && $this->isWebhooksEnabled($order->getStore())) {

                $eventType = self::EVENT_ORDERS_UPDATED;
                if ($order->getCreatedAt() == $order->getUpdatedAt()) {
                    $eventType = self::EVENT_ORDERS_CREATED;
                }

                $data = $this->orderSerializer->serialize($order);
                $this->makeRequest($eventType, $data, $order->getStore()->getId());
            }
        } catch (\Exception $ex){
            $this->logError($ex);
        }
    }

}
