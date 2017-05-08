<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 5/1/17
 * Time: 10:55 AM
 */

namespace Remarkety\Mgconnector\Observer;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\ObserverInterface;

class TriggerProductBeforeDelete extends EventMethods implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {

            if(!$this->shouldSendProductUpdates()){
                return;
            }

            $event = $observer->getEvent();
            /**
             * @var $product ProductInterface
             */
            $product = $event->getDataByKey('product');
            if(!empty($product)) {
                $storeIds = $product->getStoreIds();
                if (!empty($storeIds)) {
                    $this->_coreRegistry->register('product_stores_' . $product->getId(), $storeIds);
                }
            }
        } catch (\Exception $ex){
            $this->logError($ex);
        }
    }
}
