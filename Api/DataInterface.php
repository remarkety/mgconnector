<?php
namespace Remarkety\Mgconnector\Api;

/**
 * API Methods to support Remarkety's store sync
 */
interface DataInterface
{
    /**
     * Get All products from catalog
     *
     * @param int|null $mage_store_id
     * @param string|null $updated_at_min
     * @param string|null $updated_at_max
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $since_id
     * @param string|null $created_at_min
     * @param string|null $created_at_max
     * @param int|null $product_id
     * @param bool $enabled_only
     * @return \Remarkety\Mgconnector\Api\Data\ProductCollectionInterface
     */
    public function getProducts(
        $mage_store_id,
        $updated_at_min = null,
        $updated_at_max = null,
        $limit = null,
        $page = null,
        $since_id = null,
        $created_at_min = null,
        $created_at_max = null,
        $product_id = null,
        $enabled_only = false
    );
    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     * @param string|null $updated_at_min
     * @param string|null $updated_at_max
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $since_id
     * @param int|null $customer_id
     * @return \Remarkety\Mgconnector\Api\Data\CustomerCollectionInterface
     */
    public function getCustomers(
        $mage_store_id,
        $updated_at_min = null,
        $updated_at_max = null,
        $limit = null,
        $page = null,
        $since_id = null,
        $customer_id = null
    );
    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     * @param string|null $updated_at_min
     * @param string|null $updated_at_max
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $since_id
     * @param string|null $created_at_min
     * @param string|null $created_at_max
     * @param string|null $order_status
     * @param int|null $order_id
     * @return \Remarkety\Mgconnector\Api\Data\OrderCollectionInterface
     */
    public function getOrders(
        $mage_store_id,
        $updated_at_min = null,
        $updated_at_max = null,
        $limit = null,
        $page = null,
        $since_id = null,
        $created_at_min = null,
        $created_at_max = null,
        $order_status = null,
        $order_id = null
    );
    /**
     * Get All products from catalog
     *
     * @param int|null $mage_store_id
     * @return \Remarkety\Mgconnector\Api\Data\ProductCountCollectionInterface
     */
    public function getProductsCount($mage_store_id);
    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     * @return \Remarkety\Mgconnector\Api\Data\CustomerCountCollectionInterface
     */
    public function getCustomersCount($mage_store_id);
    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     * @return \Remarkety\Mgconnector\Api\Data\OrderCountCollectionInterface
     */
    public function getOrdersCount($mage_store_id);
    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     * @param string|null $updated_at_min
     * @param string|null $updated_at_max
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $since_id
     * @param int|null $quote_id
     * @return \Remarkety\Mgconnector\Api\Data\QuoteCollectionInterface
     */
    public function getQuotes(
        $mage_store_id,
        $updated_at_min = null,
        $updated_at_max = null,
        $limit = null,
        $page = null,
        $since_id = null,
        $quote_id = null
    );
    /**
     * Get store settings
     * @param int|null $mage_store_id
     * @return \Remarkety\Mgconnector\Api\Data\StoreSettingsInterface
     */
    public function getStoreSettings($mage_store_id);
    /**
     * Get All customers from catalog
     *
     * @return \Remarkety\Mgconnector\Api\Data\StoreOrderStatusesCollectionInterface
     */
    public function getStoreOrderStatuses();
    /**
     * Create coupon
     *
     * @param int     $ruleId
     * @param string $couponCode
     * @param string $expiration
     *
     * @return mixed $response
     */
    public function createCoupon($ruleId, $couponCode, $expiration  = null);

    /**
     * @param int|null $mage_store_id
     * @param string $configName
     * @param string $scope
     * @return string
     */
    public function getConfig($mage_store_id, $configName, $scope);

    /**
     * @param int|null $mage_store_id
     * @param string $configName
     * @param string $scope
     * @param string $newValue
     * @return string
     */
    public function setConfig($mage_store_id, $configName, $scope, $newValue);

    /**
     * @return string
     */
    public function getVersion();

    /**
     * @param int $mage_store_id
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $minId
     * @param int|null $maxId
     * @return \Remarkety\Mgconnector\Api\Data\QueueCollectionInterface
     */
    public function getQueueItems($mage_store_id, $limit = null, $page = null, $minId = null, $maxId = null);

    /**
     * @param int $mage_store_id
     * @param int|null $minId
     * @param int|null $maxId
     * @return mixed
     */
    public function deleteQueueItems($mage_store_id, $minId = null, $maxId = null);

    /**
     * @param int $mage_store_id
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $minId
     * @param int|null $maxId
     * @return int
     */
    public function retryQueueItems($mage_store_id, $limit = null, $page = null, $minId = null, $maxId = null);
}
