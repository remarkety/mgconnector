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

            if(!$this->shouldSendProductUpdates()){
                return;
            }

            $event = $observer->getEvent();
            /**
             * @var $product ProductInterface
             */
            $product = $event->getDataByKey('product');
            $eventType = self::EVENT_PRODUCTS_UPDATED;
            if($product->isObjectNew()){
                $eventType = self::EVENT_PRODUCTS_CREATED;
            }
            if(!empty($product)) {
                $storeIds = $product->getStoreIds();
                if (!empty($storeIds)) {
                    foreach ($storeIds as $storeId) {
                        if ($this->isWebhooksEnabled($storeId)) {
                            $data = $this->productSerializer->serialize($product, $storeId);

                            $this->makeRequest($eventType, $data, $storeId);
                        }
                    }
                }
            }
        } catch (\Exception $ex){
            $this->logError($ex);
        }
    }

}
