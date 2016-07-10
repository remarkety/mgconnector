<?php
namespace Remarkety\Mgconnector\Api;

/**
 * Defines a data structure representing a point, to demonstrating passing
 * more complex types in and out of a function call.
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
        $product_id = null
    );
    /**
     * Get All customers from catalog
     *
     * @param int|null $customer_id
     * @param int|null $mage_store_id
     * @param string|null $updated_at_min
     * @param string|null $updated_at_max
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $since_id
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
     * Get All customers from catalog
     *
     * @return int The sum of the numbers.
     */
    public function createCoupon();
}