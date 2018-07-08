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

class TriggerProductDelete extends EventMethods implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->startTiming(self::class);
            if(!$this->shouldSendProductUpdates()){
                return;
            }

            $event = $observer->getEvent();
            /**
             * @var $product ProductInterface
             */
            $product = $event->getDataByKey('product');
            if(!empty($product)) {
                $storeIds = $this->_coreRegistry->registry('product_stores_' . $product->getId());
                if (!empty($storeIds)) {
                    foreach ($storeIds as $storeId) {
                        if ($this->isWebhooksEnabled($storeId)) {
                            $this->makeRequest(self::EVENT_PRODUCTS_DELETE, [
                                'id' => $product->getId()
                            ], $storeId);
                        }
                    }
                }
            }
            $this->endTiming(self::class);
        } catch (\Exception $ex){
            $this->logError($ex);
        }
    }
}
