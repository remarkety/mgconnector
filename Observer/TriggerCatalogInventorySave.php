<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 5/1/17
 * Time: 10:50 AM
 */

namespace Remarkety\Mgconnector\Observer;


use Magento\Framework\Event\ObserverInterface;

class TriggerCatalogInventorySave extends EventMethods implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
//        $event = $observer->getEvent();
//        $_item = $event->getItem();
//        if ((int)$_item->getQty != (int)$_item->getOrigData('qty')) {
//            $willSend = true;
//        }
    }
}
