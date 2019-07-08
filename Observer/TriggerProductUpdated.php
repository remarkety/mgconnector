<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 4/26/17
 * Time: 1:59 PM
 */

namespace Remarkety\Mgconnector\Observer;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class TriggerProductUpdated extends EventMethods implements ObserverInterface
{

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
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
            $eventType = self::EVENT_PRODUCTS_UPDATED;

            if(!empty($product)) {
                if ($product->isObjectNew()) {
                    $eventType = self::EVENT_PRODUCTS_CREATED;
                }
                //for multistore id
                if (empty($product->getStoreId())) {
                    $storeIds = $product->getStoreIds();
                } else {
                    $storeIds = [$product->getStoreId()];
                }

                foreach ($storeIds as $storeId) {
                    if ($this->isWebhooksEnabled($storeId)) {
                        $data = $this->productSerializer->serialize($product, $storeId);

                        $this->makeRequest($eventType, $data, $storeId);
                    }
                }
            }
            $this->endTiming(self::class);
        } catch (\Exception $ex){
            $this->logError($ex);
        }
    }

}
