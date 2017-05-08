<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 5/1/17
 * Time: 10:56 AM
 */

namespace Remarkety\Mgconnector\Observer;


use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Creditmemo;

class TriggerRefundOrderInventory extends EventMethods implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if(!$this->shouldSendProductUpdates()){
                return;
            }

            $event = $observer->getEvent();
            /**
             * @var $creditMemo Creditmemo
             */
            $creditMemo = $event->getCreditmemo();
            foreach ($creditMemo->getAllItems() as $item){
                if($item->getBackToStock()) {
                    $product = $this->productSerializer->loadProduct($item->getProductId());
                    $storeIds = $product->getStoreIds();
                    if (!empty($storeIds)) {
                        foreach ($storeIds as $storeId) {
                            if ($this->isWebhooksEnabled($storeId)) {
                                $data = $this->productSerializer->serialize($product, $storeId);
                                $this->makeRequest(self::EVENT_PRODUCTS_UPDATED, $data, $storeId);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex){
            $this->logError($ex);
        }
    }
}
