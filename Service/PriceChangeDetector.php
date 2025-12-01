<?php
/**
 * Service to detect price changes during catalog rule indexing
 * Uses snapshot diff strategy to sync only products with actual price changes
 */

namespace Remarkety\Mgconnector\Service;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Serializer\ProductSerializer;
use Remarkety\Mgconnector\Observer\EventMethods;
use Psr\Log\LoggerInterface;

class PriceChangeDetector
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var ProductSerializer
     */
    protected $productSerializer;

    /**
     * @var EventMethods
     */
    protected $eventMethods;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array Snapshot of product prices before indexing
     */
    protected $snapshot = [];

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        ConfigHelper $configHelper,
        ProductSerializer $productSerializer,
        EventMethods $eventMethods,
        LoggerInterface $logger
    ) {
        $this->productRepository = $productRepository;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->configHelper = $configHelper;
        $this->productSerializer = $productSerializer;
        $this->eventMethods = $eventMethods;
        $this->logger = $logger;
    }

    /**
     * STEP 1: Capture old prices before catalog rule indexing
     * 
     * @return void
     */
    public function captureOldPrices()
    {
        $ids = $this->getIdsFromChangeLog();
        if (empty($ids)) {
            $this->logger->info('Remarkety: No products in catalogrule_product_cl for price snapshot');
            return;
        }

        $this->logger->info('Remarkety: Capturing price snapshot for ' . count($ids) . ' products');

        foreach ($ids as $id) {
            try {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
                $this->snapshot[$id] = $this->getCustomerGroupPrice($id, $websiteId);
            } catch (\Exception $e) {
                $this->logger->warning('Remarkety: Failed to capture price for product ' . $id . ': ' . $e->getMessage());
                continue;
            }
        }

        $this->logger->info('Remarkety: Captured ' . count($this->snapshot) . ' product prices');
    }

    /**
     * STEP 2: Compare prices after indexing and sync only changes
     * 
     * @return void
     */
    public function syncOnlyChanges()
    {
        if (empty($this->snapshot)) {
            $this->logger->info('Remarkety: No snapshot available, skipping price change sync');
            return;
        }

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $changedCount = 0;
        $unchangedCount = 0;

        foreach ($this->snapshot as $id => $oldPrice) {
            try {
                $newPrice = $this->getCustomerGroupPrice($id, $websiteId);
                
                // Sync only if price changed > 0.0001 (to handle floating point precision)
                if (abs($newPrice - $oldPrice) > 0.0001) {
                    $this->performExternalSync($id, $oldPrice, $newPrice);
                    $changedCount++;
                } else {
                    $unchangedCount++;
                }
            } catch (\Exception $e) {
                $this->logger->warning('Remarkety: Failed to compare price for product ' . $id . ': ' . $e->getMessage());
                continue;
            }
        }

        $this->logger->info('Remarkety: Price sync completed. Changed: ' . $changedCount . ', Unchanged: ' . $unchangedCount);
        
        // Cleanup snapshot
        $this->snapshot = [];
    }

    /**
     * Get final price for the configured customer group
     * 
     * @param int $productId
     * @param int $websiteId
     * @return float
     */
    private function getCustomerGroupPrice($productId, $websiteId)
    {
        $product = $this->productRepository->getById($productId, false, null, true);
        $product->setWebsiteId($websiteId);
        $product->setCustomerGroupId($this->configHelper->getCustomerGroupForPriceRules());
        
        // Force recalculation
        $product->setFinalPrice(null);
        
        return (float) $product->getFinalPrice();
    }

    /**
     * Get product IDs from catalogrule_product_cl (Change Log)
     * 
     * @return array
     */
    private function getIdsFromChangeLog()
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('catalogrule_product_cl');
        
        if (!$connection->isTableExists($tableName)) {
            $this->logger->warning('Remarkety: catalogrule_product_cl table does not exist');
            return [];
        }

        $select = $connection->select()
            ->from($tableName, ['entity_id'])
            ->distinct(true);

        return $connection->fetchCol($select);
    }

    /**
     * Perform external synchronization for product with price change
     * 
     * @param int $productId
     * @param float $oldPrice
     * @param float $newPrice
     * @return void
     */
    private function performExternalSync($productId, $oldPrice, $newPrice)
    {
        $this->logger->info(sprintf(
            'Remarkety: Product %d price changed from %s to %s - triggering sync',
            $productId,
            number_format($oldPrice, 4),
            number_format($newPrice, 4)
        ));

        try {
            // Load the product to get all store associations
            $product = $this->productRepository->getById($productId);
            
            // Get store IDs for this product
            if (empty($product->getStoreId())) {
                $storeIds = $product->getStoreIds();
            } else {
                $storeIds = [$product->getStoreId()];
            }

            // Send webhook for each store the product is associated with
            foreach ($storeIds as $storeId) {
                // Check if webhooks are enabled for this store
                if (!$this->configHelper->shouldSendProductUpdates()) {
                    continue;
                }

                // Serialize product data
                $data = $this->productSerializer->serialize($product, $storeId);

                // Use existing EventMethods to send the webhook
                $this->eventMethods->makeRequest(
                    EventMethods::EVENT_PRODUCTS_UPDATED,
                    $data,
                    $storeId
                );

                $this->logger->info(sprintf(
                    'Remarkety: Sent product update webhook for product %d in store %d',
                    $productId,
                    $storeId
                ));
            }
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Remarkety: Failed to sync product %d: %s',
                $productId,
                $e->getMessage()
            ));
        }
    }
}
