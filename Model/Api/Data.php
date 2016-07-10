<?php
namespace Remarkety\Mgconnector\Model\Api;

use Magento\Framework\DataObject;
use Remarkety\Mgconnector\Api\DataInterface;
use \Magento\Catalog\Model\ProductFactory;
use \Magento\Customer\Model\CustomerFactory;
use \Magento\Customer\Model\Customer;
use \Magento\Customer\Model\AddressFactory;
use \Magento\Customer\Model\ResourceModel\Address\CollectionFactory as AddressDataFactory;
use \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use \Magento\Sales\Model\OrderFactory;
use \Magento\Quote\Model\QuoteFactory;
use \Magento\Sales\Model\Order\StatusFactory;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Remarkety\Mgconnector\Model\Api\Data\StoreSettingsContact;

class Data implements DataInterface
{
    private $_productCache = [];

    protected $productCollection = false;
    protected $productCollectionFactory = false;
    protected $collectionFactory;
    protected $objectManager;
    protected $categoryFactory;
    protected $categoryMapCache = [];
    protected $imageFactory;
    protected $customerGroupFactory;
    protected $_salesOrderAddressModel;
    protected $_storeManagerInterface;
    protected $_catalogProductTypeConfigurable;
    protected $productFactory;
    protected $customerFactory;
    protected $salesOrderCollectionFactory;
    protected $entryFactory;
    protected $adressFactory;
    protected $scopeConfig;
    protected $statusFactory;
    protected $_customerCollectionFactory;
    protected $_salesOrderResourceCollectionFactory;
    protected $quoteFactory;

    protected $response_mask = [
        'products' => [
            'body_html' => 'description',
            'categories' => [
                'code',
                'name'
            ],
            "created_at" => 'created_at',
            "id" => 'entity_id',
            "image" => [
                "id",
                "product_id",
                "created_at",
                "updated_at",
                "src",
                "variant_ids"
            ],
            "images" => [
                "id",
                "product_id",
                "created_at",
                "updated_at",
                "src",
                "variant_ids"
            ],
            "options" => [
                "id",
                "name",
                "values",
            ],
            "published_at",
            "parent_id",
            "product_exists" => 'is_active',
            "sku" => 'sku',
            "tags",
            "updated_at" => 'updated_at',
            "url",
            "variants" => [
                "barcode",
                "currency",
                "created_at",
                "fulfillment_service",
                "id",
                "image",
                "inventory_quantity",
                "price",
                "product_id",
                "sku",
                "taxable",
                "title",
                "option1",
                "updated_at",
                "requires_shipping",
                "weight",
                "weight_unit"
            ],
            "vendor"
        ],
        'customers' => [
            "accepts_marketing",
            "birthdate" => 'dob',
            "created_at" => 'created_at',
            "default_address" => [
                "country" => 'country_id',
                "country_code" => 'country_id',
                "province_code" => 'region_id',
                "zip" => 'postcode',
                "phone" => 'telephone'
            ],
            "email" => 'email',
            "first_name" => 'firstname',
            "gender" => 'gender',
            "groups" => [
                "id",
                "name"
            ],
            "id" => 'entity_id',
            "info",
            "last_name" => 'lastname',
            "updated_at" => 'updated_at',
            "tags",
            "title" => 'prefix',
            "verified_email" => 'confirmation'
        ],
        'orders' => [
            "created_at" => 'created_at',
            "currency" => 'order_currency_code',
            "customer" => [
                "accepts_marketing",
                "birthdate",
                "created_at",
                "default_address" => [
                    "country",
                    "country_code",
                    "province_code",
                    "zip",
                    "phone"
                ],
                "email",
                "first_name",
                "gender",
                "groups" => [
                    "id",
                    "name"
                ],
                "id",
                "info",
                "last_name",
                "updated_at",
                "tags",
                "title",
                "verified_email"
            ],
            "discount_codes" => 'coupon_code',
            "email" => 'customer_email',
            "fulfillment_status",
            "id" => 'entity_id',
            "line_items" => [
                "product_id" => 'product_id',
                "quantity" => 'qty_ordered',
                "sku" => 'sku',
                "name" => 'name',
                "title",
                "variant_title",
                "vendor",
                "price" => 'price',
                "taxable",
                "tax_lines" => [
                    "title",
                    "price",
                    "rate"
                ]
            ],
            "note" => 'customer_note',
            "shipping_lines" => [
                "title",
                "price",
                "code"
            ],
            "status" => [
                "code",
                "name"
            ],
            "subtotal_price" => 'subtotal',
            "tax_lines" => [
                "title",
                "price",
                "rate"
            ],
            "taxes_included" => 'base_tax_amount',
            "test",
            "total_discounts" => 'base_discount_amount',
            "total_line_items_price",
            "total_price" => 'grand_total',
            "total_shipping" => 'shipping_amount',
            "total_tax",
            "total_weight" => 'weight',
            "updated_at" => 'updated_at'
        ],
        "carts" => [

            "abandoned_checkout_url",
            "billing_address" => [
                "country" => 'country_id',
                "country_code" => 'country_id',
                "province_code" => 'region_id',
                "zip" => 'postcode',
                "phone" => 'telephone'
            ],

            "accepts_marketing",
            "cart_token",
            "created_at" => "created_at",
            "currency" => "global_currency_code",
            "discount_codes" => 'coupon_code',
            "email" => 'customer_email',
            "fulfillment_status",
            "id" => 'entity_id',
            "line_items" => [
                "product_id" => 'product_id',
                "quantity" => 'qty',
                "sku" => 'sku',
                "name" => 'name',
                "title",
                "variant_title",
                "vendor",
                "price" => 'price',
                "taxable",
                "tax_lines" => [
                    "title",
                    "price",
                    "rate"
                ]
            ],
            "note" => 'customer_note',
            "shipping_address" => [
                "country" => 'country_id',
                "country_code" => 'country_id',
                "province_code" => 'region_id',
                "zip" => 'postcode',
                "phone" => 'telephone'
            ],
            "shipping_lines" => 'shipping_method',
            "subtotal_price" => 'subtotal',
            "tax_lines",
            "taxes_included",
            "total_discounts",
            "total_line_items_price",
            "total_price" => 'grand_total',
            "total_shipping",
            "total_tax" => 'tax_amount',
            "total_weight" => 'weight',
            "updated_at" => 'updated_at'
        ]
    ];

    public function __construct(ProductFactory $productFactory,
                                \Remarkety\Mgconnector\Api\Data\ProductCollectionInterfaceFactory $searchResultFactory,
                                \Remarkety\Mgconnector\Api\Data\CustomerCollectionInterfaceFactory $customerResultFactory,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
                                \Magento\Catalog\Model\Product $productModelCollection,
                                CustomerFactory $customerFactory,
                                OrderFactory $salesOrderCollectionFactory,
                                QuoteFactory $quoteFactory,
                                StatusFactory $statusFactory,
                                ScopeConfigInterface $scopeConfig,
                                \Magento\Catalog\Model\CategoryFactory $categoryFactory,
                                \Magento\Framework\ObjectManagerInterface $interface,
                                \Magento\Catalog\Model\Product\ImageFactory $imageFactory,
                                \Magento\Catalog\Model\Product\Gallery\GalleryManagement $entryFactory,
                                AddressFactory $addressFactory,
                                AddressDataFactory $addressDataFactory,
                                \Magento\Customer\Model\Group $customerGroupModel,
                                Customer $customer,
                                \Magento\Newsletter\Model\Subscriber $subscriber,
                                \Magento\Sales\Model\Order\Address $salesOrderAddress,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
                                CustomerCollectionFactory $customerCollectionFactory,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderResourceCollectionFactory
    )
    {
        $this->productFactory = $productFactory;
        $this->productCollectionFactory = $searchResultFactory;
        $this->customerFactory = $customerFactory;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->quoteFactory = $quoteFactory;
        $this->statusFactory = $statusFactory;
        $this->collectionFactory = $collectionFactory;
        $this->customerResultFactory = $customerResultFactory;
        $this->scopeConfig = $scopeConfig;
        $this->product = $productModelCollection;
        $this->objectManager = $interface;
        $this->categoryFactory = $categoryFactory;
        $this->imageFactory = $imageFactory;
        $this->entryFactory = $entryFactory;
        $this->adressFactory = $addressFactory;
        $this->adressDataFactory = $addressDataFactory;
        $this->customer = $customer;
        $this->customerGroupFactory = $customerGroupModel;
        $this->subscriber = $subscriber;
        $this->_salesOrderAddressModel = $salesOrderAddress;
        $this->_storeManagerInterface = $storeManager;
        $this->_catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->_salesOrderResourceCollectionFactory = $salesOrderResourceCollectionFactory;
    }

    public function getParentId($id)
    {
        $parentByChild = $this->_catalogProductTypeConfigurable->getParentIdsByChild($id);
        if (isset($parentByChild[0])) {
            $id = $parentByChild[0];
            return $id;
        }
        return false;
    }
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
     * @return array $collection
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
    )
    {

        $pageNumber = null;
        $pageSize = null;

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*');

        if($mage_store_id !== null){
            $collection->addStoreFilter($mage_store_id);
        }

        if ($updated_at_min != null) {
            $collection->addAttributeToFilter('updated_at', ['gt' => $updated_at_min]);
        }

        if ($updated_at_max != null) {
            $collection->addAttributeToFilter('updated_at', ['lt' => $updated_at_max]);
        }

        if ($since_id != null) {
            $collection->addAttributeToFilter('entity_id', ['gt' => $since_id]);
        }

        if ($created_at_min != null) {
            $collection->addAttributeToFilter('created_at', ['gt' => $created_at_min]);
        }

        if ($created_at_max != null) {
            $collection->addAttributeToFilter('created_at', ['lt' => $created_at_max]);
        }

        if ($product_id != null) {
            $collection->addAttributeToFilter('entity_id', $product_id);
        }

        if ($limit != null) {
            $pageNumber = 1;        // Note that page numbers begin at 1
            $pageSize = $limit;
        }

        if ($page != null) {
            if (!is_null($pageSize)) {
                $pageNumber = $page + 1;    // Note that page numbers begin at 1
            }
        }

        if (!is_null($pageSize)) $collection->setPage($pageNumber, $pageSize);


        $map = $this->response_mask;
        $productsArray = [];
        foreach ($collection AS $row) {
            $prod = [];
            $mappedArray = $row->getData();
            if ($row->getCategoryIds()) {
                foreach ($row->getCategoryIds() AS $category_id) {
                    $prod['categories'][] = $this->getCategory($category_id);
                }
            }

            //find values from mapping array
            foreach ($map['products'] as $element => $value) {
                if (!is_array($value)) {
                    if (array_key_exists($value, $mappedArray)) {
                        $prod[$element] = $mappedArray[$value];
                    }
                }
            }

            $prod['image'] = $this->getImage($row);
            $prod['images'] = $this->getMediaGalleryImages($row);

            $prod['body_html'] = $row->getDescription();
            $prod['id'] = $row->getId();

            $parent_id = $this->getParentId($row->getId());
            if ($row->getTypeId() == 'simple' && $parent_id) {
                $parentProductData = $this->productFactory->create()->load($parent_id);
                if ($parentProductData->getId()) {
                    $prod['url'] = $parentProductData->getProductUrl();
                    $prod['title'] = $parentProductData->getName();
                    $prod['parent_id'] = $parent_id;
                }
            } else {
                $prod['url'] = $row->getProductUrl();
                $prod['title'] = $row->getName();
            }

            $productsArray[] = $prod;
        }
        $object = new DataObject();
        $object->setProducts($productsArray);
        return $object;
    }

    private function getCategory($category_id)
    {
        if (!isset($this->categoryMapCache[$category_id])) {
            $category = $this->categoryFactory->create()->load($category_id);
            $this->categoryMapCache[$category_id] = $category->getName();
        }
        if (!isset($this->categoryMapCache[$category_id])) return false;

        return ['code' => $category_id, 'name' => $this->categoryMapCache[$category_id]];
    }

    public function getMediaGalleryImages($product)
    {
        $images = $this->entryFactory->getList($product->getSku());
        $imageDet = [];
        $imagesData = [];
        if ($images) {
            foreach ($images as $imageAttr) {
                $imagesData['id'] = $imageAttr['id'];
                $imagesData['product_id'] = $imageAttr['entity_id'];
                $imagesData['src'] = $this->getMediaUrl() . 'catalog/product' . $imageAttr['file'];;
                $imageDet[] = $imagesData;
            }
            return $imageDet;
        } return;
    }

    public function getImage($product)
    {
        $images = $this->entryFactory->getList($product->getSku());
        $imageDet = [];
        $imagesData = [];
        if($images) {
            foreach ($images as $imageAttr) {
                if ($imageAttr['types']) {
                    foreach ($imageAttr['types'] as $type) {
                        if ($type == 'image') {
                            $imagesData['id'] = $imageAttr['id'];
                            $imagesData['product_id'] = $imageAttr['entity_id'];
                            $imagesData['src'] = $this->getMediaUrl() . 'catalog/product' . $imageAttr['file'];
                            $imageDet = $imagesData;
                        }

                    }
                }
            }
            return $imageDet;
        }
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->_storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $mediaUrl;
    }

    /**
     * Get All products from catalog
     *
     * @param int|null $mage_store_id
     * @param string|null $updated_at_min
     * @param string|null $updated_at_max
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $since_id
     * @param int|null $customer_id
     * @return array $customerData
     */
    public function getCustomers(
        $mage_store_id,
        $updated_at_min = null,
        $updated_at_max = null,
        $limit = null,
        $page = null,
        $since_id = null,
        $customer_id = null
    )
    {

        $pageNumber = null;
        $pageSize = null;

        $customerData = $this->_customerCollectionFactory->create();

        if ($customer_id !== null) {
            $customerData->addFieldToFilter('entity_id', $customer_id);
        }
        $customerData->addFieldToFilter('store_id', ['eq' => $mage_store_id]); //$mage_store_id));
        if ($updated_at_min != null) {
            $customerData->addAttributeToFilter('updated_at', ['gt' => $updated_at_min]);
        }

        if ($updated_at_max != null) {
            $customerData->addAttributeToFilter('updated_at', ['lt' => $updated_at_max]);
        }

        if ($since_id != null) {
            $customerData->addAttributeToFilter('entity_id', ['gt' => $since_id]);
        }

        if ($limit != null) {
            $pageNumber = 1;        // Note that page numbers begin at 1 in Magento
            $pageSize = $limit;
        }

        if ($page != null) {
            if (!is_null($pageSize)) {
                $pageNumber = $page + 1;    // Note that page numbers begin at 1 in Magento
            }
        }

        if (!is_null($pageSize)) {
            $customerData->setPage($pageNumber, $pageSize);
        }

        $customerArray = [];
        $map = $this->response_mask;

        foreach ($customerData AS $customer) {
            $customers = [];
            $mappedCustomer = $customer->getData();

            $newsletter = $this->subscriber->load($customer->getId(), 'customer_id');
            foreach ($map['customers'] as $element => $value) {
                $mappedCustomer['id'] = $customer->getId();

                if (!is_array($value)) {
                    if (array_key_exists($value, $mappedCustomer)) {
                        if ($element == 'gender' && $mappedCustomer['gender'] == '0') {
                            $mappedCustomer['gender'] = 'Not Selected';
                        }
                        if ($element == 'gender' && $mappedCustomer['gender'] == '1') {
                            $mappedCustomer['gender'] = 'M';
                        }
                        if ($element == 'gender' && $mappedCustomer['gender'] == '2') {
                            $mappedCustomer['gender'] = 'F';
                        }
                        $customers[$element] = $mappedCustomer[$value];
                    }
                }
            }
            $customers['default_address'] = $this->getCustomerAddresses($customer->getId());
            $group = $this->customerGroupFactory->load($customer->getGroupId());
            $customers['groups'] = array();
            $customers['groups'][] = [
                'id' => $group->getId(),
                'name' => $group->getCustomerGroupCode(),
            ];
            if ($newsletter->isSubscribed()) {
                $customers['accepts_marketing'] = 'true';
            } else {
                $customers['accepts_marketing'] = 'false';
            }
            $customerArray[] = $customers;
        }
        $object = new DataObject();
        $object->setCustomers($customerArray);
        return $object;
    }


    public function getCustomerAddresses($customer_id){

        $customerAddresses = $this->adressFactory->create()->load($customer_id, 'customer_id')->getData();
        $addressData = [];
        foreach ($this->response_mask['customers']['default_address'] as $key => $value) {
            if (!is_array($value)) {
                if (array_key_exists($value, $customerAddresses)) {
                    $addressData[$key] = $customerAddresses[$value];
                }
            }
        }
        return $addressData;
    }

    public function mapCustomer($customer, $isObject = false)
    {
        if ($isObject) {
            $customerData = $customer;
        } else {
            $customerData = $this->customerFactory->create()->load($customer);
        }
        $customer_id = $customerData->getId();
        $customers = [];
        $mappedCustomer = $customerData->getData();
        $mappedCustomer['id'] = $customer_id;

        foreach ($this->response_mask['customers'] as $element => $value) {
            if (!is_array($value)) {
                if (array_key_exists($value, $mappedCustomer)) {
                    if ($element == 'gender' && $mappedCustomer['gender'] == '0') {
                        $mappedCustomer['gender'] = 'Not Selected';
                    }
                    if ($element == 'gender' && $mappedCustomer['gender'] == '1') {
                        $mappedCustomer['gender'] = 'M';
                    }
                    if ($element == 'gender' && $mappedCustomer['gender'] == '2') {
                        $mappedCustomer['gender'] = 'F';
                    }
                    $customers[$element] = $mappedCustomer[$value];
                }
            }
        }
        $customers['default_address'] = $this->getCustomerAddresses($customer_id);
        return $customers;
    }

    public function getCustomerDataById($id = false)
    {
        $customerData = $this->customerFactory->create()->load($id);
        $customerArray = [];

        if($customerData->getId()){
            $customerArray = $this->mapCustomer($customerData, true);
        }
        return $customerArray;
    }

    public function getGuestBillingAddressData($billingAddressId)
    {
        return $this->_salesOrderAddressModel->load($billingAddressId);
    }

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
     * @return array $orders
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
    )
    {
        $pageNumber = null;
        $pageSize = null;

        $orders = $this->_salesOrderResourceCollectionFactory->create();

        $orders->addFieldToFilter('main_table.store_id', array('eq' => $mage_store_id));
        if ($updated_at_min != null) {
            $orders->addAttributeToFilter('main_table.updated_at', array('gt' => $updated_at_min));
        }

        if ($updated_at_max != null) {
            $orders->addAttributeToFilter('main_table.updated_at', array('lt' => $updated_at_max));
        }

        if ($since_id != null) {
            $orders->addAttributeToFilter('main_table.entity_id', array('gt' => $since_id));
        }

        if ($created_at_min != null) {
            $orders->addAttributeToFilter('main_table.created_at', array('gt' => $created_at_min));
        }

        if ($created_at_max != null) {
            $orders->addAttributeToFilter('main_table.created_at', array('lt' => $created_at_max));
        }

        if ($order_status != null) {
            $orders->addAttributeToFilter('main_table.status', $order_status);
        }
        if ($order_id != null) {
            $orders->addAttributeToFilter('main_table.entity_id', $order_id);
        }

        if ($limit != null) {
            $pageNumber = 1;        // Note that page numbers begin at 1
            $pageSize = $limit;
        }

        if ($page != null) {
            if (!is_null($pageSize)) {
                $pageNumber = $page + 1;    // Note that page numbers begin at 1
            }
        }

        if (!is_null($pageSize)) {
            $orders->setPage($pageNumber, $pageSize);

        }

        $map = $this->response_mask;
        $ordersArray = [];
        foreach ($orders as $order) {
            $items = $order->getAllItems();

            $ord = [];
            $orderDetails = $order->getData();
            foreach ($map['orders'] as $element => $value) {
                if (!is_array($value)) {
                    if ($value == 'coupon_code') {
                        if ($orderDetails['coupon_code']) {
                            $ord['discount_codes'] = [
                                [
                                    'code' => $orderDetails['coupon_code'],
                                    'amount' => isset($orderDetails['discount_amount']) ? $orderDetails['discount_amount'] : 0
                                ]
                            ];
                        } else {
                            $ord['discount_codes'] = [];
                        }
                    } elseif (array_key_exists($value, $orderDetails)) {
                        $ord[$element] = $orderDetails[$value];
                    }
                }
            }
            if ($order->getCustomerId()) {
                $ord['customer'] = $this->getCustomerDataById($order->getCustomerId());
            } else {
                $billingAddressData = $this->getGuestBillingAddressData($order->getBillingAddressId());

                $ord['customer']['email'] = $billingAddressData->getEmail();
                $ord['customer']['first_name'] = $billingAddressData->getFirstname();
                $ord['customer']['last_name'] = $billingAddressData->getLastname();
                $ord['customer']['title'] = $billingAddressData->getPrefix();

                $ord['customer']['default_address']['country'] = $billingAddressData->getCountryId();
                $ord['customer']['default_address']['country_code'] = $billingAddressData->getCountryId();
                $ord['customer']['default_address']['province_code'] = $billingAddressData->getRegionId();
                $ord['customer']['default_address']['zip'] = $billingAddressData->getPostcode();
                $ord['customer']['default_address']['phone'] = $billingAddressData->getTelephone();
            }
            $ord['line_items'] = [];
            foreach($items as $item){
                $newItem = [];
                $itemElement = $item->getData();
                foreach ($map['orders']['line_items'] as $element => $value) {
                    if (!is_array($value)) {
                        if (array_key_exists($value, $itemElement)) {
                            $newItem[$element] = $itemElement[$value];
                        }
                    }
                }
                $ord['line_items'][] = $newItem;
            }
            $ord['status']= $this->getStoreOrderStatusesByCode($orderDetails['status']);
            $ordersArray[]= $ord;
        }

        $object = new DataObject();
        $object->setOrders($ordersArray);
        return $object;
    }
    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     * @return int customers.
     */
    public function getProductsCount($mage_store_id)
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('entity_id');
        $collection->addStoreFilter($mage_store_id);
        //$collection->addStoreFilter($mage_store_id);
//        $collection->addFieldToFilter('main_table.store_id', array('eq' => $mage_store_id));

        $object = new DataObject();
        $object->setCount(count($collection));
        return $object;
    }

    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     * @return int customers.
     */
    public function getCustomersCount($mage_store_id)
    {
        $customerData = $this->_customerCollectionFactory->create();
        $customerData->addFieldToFilter('store_id', array('eq' => $mage_store_id));
        $object = new DataObject();
        $object->setCount(count($customerData));
        return $object;
    }
    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     * @return int customers.
     */
    public function getOrdersCount($mage_store_id)
    {
        $orders = $this->_salesOrderResourceCollectionFactory->create();
        $orders->addFieldToFilter('store_id', array('eq' => $mage_store_id));

        $object = new DataObject();
        $object->setCount(count($orders));
        return $object;
    }
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
     * @return array list of quotes.
     */
    public function getQuotes(
        $mage_store_id,
        $updated_at_min = null,
        $updated_at_max = null,
        $limit = null,
        $page = null,
        $since_id = null,
        $quote_id = null
    )
    {
        $pageNumber = null;
        $pageSize = null;

        $quotes = $this->quoteFactory->create()->getCollection();
        $quotes->addFieldToFilter('is_active' , 1);

        if ($mage_store_id != null) {
            $quotes->addFieldToFilter('store_id', array('eq' => $mage_store_id));
        }

        if ($updated_at_min != null) {
            $quotes->addFieldToFilter('main_table.updated_at', array('gt' => $updated_at_min));
        }

        if ($updated_at_max != null) {
            $quotes->addFieldToFilter('main_table.updated_at', array('lt' => $updated_at_max));
        }

        if ($since_id != null) {
            $quotes->addFieldToFilter('main_table.entity_id', array('gt' => $since_id));
        }

        if ($limit != null) {
            $pageNumber = 1;        // Note that page numbers begin at 1
            $pageSize = $limit;
        }

        if ($page != null) {
            if (!is_null($pageSize)) {
                $pageNumber = $page + 1;    // Note that page numbers begin at 1
            }
        }

        if (!is_null($pageSize)){
            $quotes->setPageSize($pageSize)->setCurPage($pageNumber);
        }

        if($quote_id !== null) {
            $quotes->addFieldToFilter('entity_id', $quote_id);
        }
        $map = $this->response_mask;

        $quoteCartArray = [];
        foreach($quotes as $quote) {

            $quoteData = $quote->getData();
            $quoteArray = [];
            foreach ($map['carts'] as $element => $value) {
                if (!is_array($value)) {
                    if (array_key_exists($value, $quoteData)) {
                        $quoteArray[$element] = $quoteData[$value];
                    }
                }
            }

            $defaultBilling = $quote->getBillingAddress()->getData();
            $defaultShipping = $quote->getShippingAddress()->getData();
            foreach ($map['carts']['billing_address'] as $element => $value) {
                if (!is_array($value)) {

                    if (array_key_exists($value, $defaultBilling)) {
                        $quoteArray['billing_address'][$element] = $defaultBilling[$value];
                    }
                }
            }
            foreach ($map['carts']['shipping_address'] as $element => $value) {
                if (!is_array($value)) {
                    if (array_key_exists($value, $defaultShipping)) {
                        $quoteArray['shipping_address'][$element] = $defaultShipping[$value];
                    }
                }
            }
            $itemsCollection = $quote->getItemsCollection();
            $customer = $this->mapCustomer($quote->getCustomerId());
            $quoteArray['customer'] = $customer;

            $itemArray = [];
            foreach ($itemsCollection as $item) {
                if (($item->getData('parent_item_id') || $item->getData('parent_item_id') == null) && $item->getData('product_type') == 'simple') {
                    $itemsData = $item->getData();
                    $itemData = [];
                    foreach ($map['carts']['line_items'] as $element => $value) {
                        if (!is_array($value)) {
                            if (array_key_exists($value, $itemsData)) {
                                $itemData[$element] = $itemsData[$value];
                            }
                        }
                    }
                    $itemArray[] = $itemData;
                }
            }
            $quoteArray['line_items'] = $itemArray;
            $quoteCartArray[] = $quoteArray;
        }
        $object = new DataObject();
        $object->setCarts($quoteCartArray);
        return $object;
    }

    /**
     * Get store settings
     * @param int|null $mage_store_id
     * @return DataObject
     */
    public function getStoreSettings($mage_store_id)
    {

        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Store\Model\StoreManagerInterface $manager */
        $manager = $om->get('Magento\Store\Model\StoreManagerInterface');
        /**
         * @var \Magento\Store\Model\Store;
         */
        $store = $manager->getStore($mage_store_id);

        $baseUrl = $store->getConfig('web/unsecure/base_url');
        $locale = $store->getConfig('general/locale/code');
        $timezone = $store->getConfig('general/locale/timezone');
        $baseCurrency = $store->getConfig('currency/options/base');
        $name = $store->getConfig('general/store_information/name');
        $logo_url = $store->getConfig('design/header/logo_src');
        $country_id = $store->getConfig('general/store_information/country_id');
        $region_id = $store->getConfig('general/store_information/region_id');
        $city = $store->getConfig('general/store_information/city');
        $address1 = $store->getConfig('general/store_information/street_line1');
        $address2 = $store->getConfig('general/store_information/street_line2');
        $zip = $store->getConfig('general/store_information/postcode');
        $phone = $store->getConfig('general/store_information/phone');
        $contact_name = $store->getConfig('trans_email/ident_general/name');
        $contact_email = $store->getConfig('trans_email/ident_general/email');

        $contact = new StoreSettingsContact();
        $contact->email = $contact_email;
        $contact->name = $contact_name;
        $contact->phone = $phone;

        $address = new DataObject();
        $address->setCountry($country_id);
        $address->setState($region_id);
        $address->setCity($city);
        $address->setAddress_1($address1);
        $address->setAddress_2($address2);
        $address->setZip($zip);

        $logo = (!empty($logo_url) ? $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR . '/' . $logo_url : '');
        $configData = array(
            'domain' => $baseUrl,
            'store_front_url' => $store->getBaseUrl(),
            'name' => $name,
            'logo_url' => $logo,
            'contact_info'=> $contact,
            'timezone'=>$timezone,
            'currency'=>$baseCurrency,
            'locale' =>$locale,
            'address'=> $address,
            'order_statuses'=>$this->getStoreOrderStatuses()
        );

        $object = new DataObject();
        $object->setData($configData);

        return $object;
    }
    /**
     * Get All customers from catalog
     *
     * @return array $orderStatuses
     */
    public function getStoreOrderStatuses()
    {
        $orderStatuses = $this->statusFactory->create()->getCollection();

        $statuses = [];
        foreach($orderStatuses as $status){
            $mappedStatuses['code'] = $status->getData('status');
            $mappedStatuses['name'] = $status->getData('label');
            $statuses[] = $mappedStatuses;
        }
        return $statuses;
    }

    public function getStoreOrderStatusesByCode($code)
    {

        $statuses = $this->getStoreOrderStatuses();
        foreach ($statuses as $status) {
            if ($status['code'] == $code) {
                return $status;
            }
        }
    }

    public function createCoupon(){

    }
}