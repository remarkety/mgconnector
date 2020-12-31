<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 5/1/17
 * Time: 10:54 AM
 */

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class TriggerRevertQuoteInventory extends EventMethods implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $this->startTiming(self::class);
            if (!$this->shouldSendProductUpdates()) {
                return;
            }

            /**
             * @var $order Order
             */
            $order = $observer->getEvent()->getOrder();
            if ($order) {
                foreach ($order->getAllItems() as $item) {
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
            $this->endTiming(self::class);
        } catch (\Exception $ex) {
            $this->logError($ex);
        }
    }
}
