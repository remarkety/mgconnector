<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 5/1/17
 * Time: 10:55 AM
 */

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;

class TriggerCancelOrderItem extends EventMethods implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
//        $event = $observer->getEvent();
    }
}
