<?php

namespace Remarkety\Mgconnector\Model\Api;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface as OrderCollectionFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Helper\Coupon;
use Magento\SalesRule\Model\CouponFactory;
use Magento\Store\Model\StoreManagerInterface;
use Remarkety\Mgconnector\Api\Data\QueueInterface;
use Remarkety\Mgconnector\Api\DataInterface;
use Remarkety\Mgconnector\Api\QueueRepositoryInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Helper\Data as DataHelper;
use Remarkety\Mgconnector\Helper\DataOverride;
use Remarkety\Mgconnector\Helper\Recovery;
use Remarkety\Mgconnector\Helper\RewardPointsFactory;
use Remarkety\Mgconnector\Model\Api\Data\StoreSettingsContact;
use Remarkety\Mgconnector\Model\ResourceModel\Queue\Collection;
use Remarkety\Mgconnector\Observer\EventMethods;
use Remarkety\Mgconnector\Resolver\ProductDataResolver;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CheckSubscriberTrait;

class Data implements DataInterface
{
    use CheckSubscriberTrait;

    private const RESPONSE_MASK = [
        'products' => [
            'body_html' => 'description',
            'categories' => [
                'code',
                'name'
            ],
            "created_at" => 'created_at',
            "id" => 'entity_id',
            "price" => "price",
            "sale_price_with_tax" => "sale_price_with_tax",
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
                "sale_price_with_tax",
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
            "coupon_code" => 'coupon_code',
            "email" => 'customer_email',
            "fulfillment_status",
            "id" => 'entity_id',
            "line_items" => [
                "product_id" => "product_id",
                "quantity" => "qty_ordered",
                "sku" => "sku",
                "name" => "name",
                "price" => "price"
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
            "total_tax" => "tax_amount",
            "order_discount" => 'discount_amount',
            "total_line_items_price",
            "total_price" => 'grand_total',
            "total_shipping" => 'shipping_amount',
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
            "coupon_code" => "coupon_code",
            "email" => 'customer_email',
            "fulfillment_status",
            "id" => 'entity_id',
            "line_items" => [
                "product_id" => 'product_id',
                "quantity" => 'qty',
                "sku" => 'sku',
                "name" => 'name',
                "variant_title",
                "price" => 'price',
                "tax_amount" => "tax_amount"
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
            "total_price" => 'grand_total',
            "total_shipping",
            "total_tax" => 'tax_amount',
            "total_weight" => 'weight',
            "updated_at" => 'updated_at'
        ]
    ];

    /**
     * @var EventMethods
     */
    private $eventMethods;

    /**
     * @var QueueRepositoryInterface
     */
    private $queueRepository;

    /**
     * @var Collection
     */
    private $queueCollection;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var StatusFactory
     */
    private $statusFactory;

    /**
     * @var CouponFactory
     */
    private $couponFactory;

    /**
     * @var ConfigInterface
     */
    private $resourceConfig;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var Recovery
     */
    private $recoveryHelper;

    /**
     * @var AddressSerializer
     */
    private $addressSerializer;

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var Subscriber
     */
    private $subscriber;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var DataOverride
     */
    private $dataOverride;

    /**
     * @var CustomerRewardPointsManagementInterface
     */
    private $customerRewardPointsService;

    /**
     * @var ProductDataResolver
     */
    private $productDataResolver;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var ConfigurableResource
     */
    private $configurableResource;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param CustomerFactory $customerFactory
     * @param QuoteFactory $quoteFactory
     * @param StatusFactory $statusFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param GroupRepositoryInterface $groupRepository
     * @param StoreManagerInterface $storeManager
     * @param ConfigurableResource $configurableResource
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param RuleRepositoryInterface $ruleRepository
     * @param CouponFactory $couponFactory
     * @param ConfigInterface $resourceConfig
     * @param TypeListInterface $cacheTypeList
     * @param Collection $queueCollection
     * @param QueueRepositoryInterface $queueRepository
     * @param EventMethods $eventMethods
     * @param DataHelper $dataHelper
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     * @param Recovery $recoveryHelper
     * @param AddressSerializer $addressSerializer
     * @param ConfigHelper $configHelper
     * @param DataOverride $dataOverride
     * @param RewardPointsFactory $rewardPointsFactory
     * @param SubscriberFactory $subscriberFactory
     * @param ProductDataResolver $productDataResolver
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CustomerFactory $customerFactory,
        QuoteFactory $quoteFactory,
        StatusFactory $statusFactory,
        ScopeConfigInterface $scopeConfig,
        GroupRepositoryInterface $groupRepository,
        StoreManagerInterface $storeManager,
        ConfigurableResource $configurableResource,
        CustomerCollectionFactory $customerCollectionFactory,
        OrderCollectionFactory $orderCollectionFactory,
        RuleRepositoryInterface $ruleRepository,
        CouponFactory $couponFactory,
        ConfigInterface $resourceConfig,
        TypeListInterface $cacheTypeList,
        Collection $queueCollection,
        QueueRepositoryInterface $queueRepository,
        EventMethods $eventMethods,
        DataHelper $dataHelper,
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry,
        Recovery $recoveryHelper,
        AddressSerializer $addressSerializer,
        ConfigHelper $configHelper,
        DataOverride $dataOverride,
        RewardPointsFactory $rewardPointsFactory,
        SubscriberFactory $subscriberFactory,
        ProductDataResolver $productDataResolver
    ) {
        $this->dataHelper = $dataHelper;
        $this->eventMethods = $eventMethods;
        $this->queueRepository = $queueRepository;
        $this->queueCollection = $queueCollection;
        $this->cacheTypeList = $cacheTypeList;
        $this->resourceConfig = $resourceConfig;
        $this->customerFactory = $customerFactory;
        $this->quoteFactory = $quoteFactory;
        $this->statusFactory = $statusFactory;
        $this->collectionFactory = $collectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->configurableResource = $configurableResource;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->ruleRepository = $ruleRepository;
        $this->couponFactory = $couponFactory;
        $this->stockRegistry = $stockRegistry;
        $this->productRepository = $productRepository;
        $this->recoveryHelper = $recoveryHelper;
        $this->addressSerializer = $addressSerializer;
        $this->configHelper = $configHelper;
        $this->dataOverride = $dataOverride;
        $this->customerRewardPointsService = $rewardPointsFactory->create();
        $this->subscriberFactory = $subscriberFactory;
        $this->productDataResolver = $productDataResolver;
        $this->groupRepository = $groupRepository;
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
     * @param bool $enabled_only
     *
     * @return DataObject
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
    ) {
        $this->storeManager->setCurrentStore($mage_store_id);

        $pageNumber = null;
        $pageSize = null;

        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*');

        if ($mage_store_id !== null) {
            $collection->addStoreFilter($mage_store_id);
        }

        if ($updated_at_min != null) {
            $collection->addAttributeToFilter('updated_at', ['gt' => $this->convertTime($updated_at_min)]);
        }

        if ($updated_at_max != null) {
            $collection->addAttributeToFilter('updated_at', ['lt' => $this->convertTime($updated_at_max)]);
        }

        if ($since_id != null) {
            $collection->addAttributeToFilter('entity_id', ['gt' => $since_id]);
        }

        if ($created_at_min != null) {
            $collection->addAttributeToFilter('created_at', ['gt' => $this->convertTime($created_at_min)]);
        }

        if ($created_at_max != null) {
            $collection->addAttributeToFilter('created_at', ['lt' => $this->convertTime($created_at_max)]);
        }

        if ($product_id != null) {
            $collection->addAttributeToFilter('entity_id', $product_id);
        }

        if ($enabled_only) {
            $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
            $collection->addAttributeToFilter('visibility', ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]);
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
            $collection->setPage($pageNumber, $pageSize);
        }

        $vendorAttr = $collection->getResource()->getAttribute('vendor');
        if (!$vendorAttr) {
            $vendorAttr = $collection->getResource()->getAttribute('brand');
        }
        $manufacturerAttr = $collection->getResource()->getAttribute('manufacturer');

        $map = static::RESPONSE_MASK;
        $productsArray = [];
        foreach ($collection as $row) {
            $prod = [];
            $mappedArray = $row->getData();
            if ($row->getCategoryIds()) {
                foreach ($row->getCategoryIds() as $category_id) {
                    $prod['categories'][] = $this->dataHelper->getCategory($category_id, $mage_store_id);
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

            //product_exists
            $visibility = array_key_exists('visibility', $mappedArray) ? $mappedArray['visibility'] : 1;
            $status = array_key_exists('status', $mappedArray) ? $mappedArray['status'] : 1;

            $active = true;
            if ($status === Status::STATUS_DISABLED || $visibility === Visibility::VISIBILITY_NOT_VISIBLE) {
                $active = false;
            }
            $parentId = $this->dataHelper->getParentId($row->getId());

            $prod['product_exists'] = $active;
            $prod['image'] = $this->dataHelper->getImage($row);
            $prod['images'] = $this->productDataResolver->getImages($parentId, $row);
            $prod['body_html'] = $row->getDescription();
            $prod['id'] = $row->getId();
            $prod['sale_price_with_tax'] = $this->getFinalPrice($row);
            $prod['title'] = $this->productDataResolver->getTitle($parentId, $row);
            $prod['url'] = $this->productDataResolver->getUrl($parentId, $row);
            !$parentId ?: $prod['parent_id'] = $parentId;

            $variants = [];
            if ($row->getTypeId() == Configurable::TYPE_CODE) {
                //configurable products sends variants
                $childrenIdsGroups = $this->configurableResource->getChildrenIds($row->getId());
                if (isset($childrenIdsGroups[0])) {
                    $childrenIds = $childrenIdsGroups[0];
                    foreach ($childrenIds as $childId) {
                        $childProd = $this->productRepository->getById($childId);
                        $stock = $this->stockRegistry->getStockItem($childId);

                        $created_at_child = new \DateTime($childProd->getCreatedAt());
                        $updated_at_child = new \DateTime($childProd->getUpdatedAt());

                        $variants[] = [
                            'id' => $childProd->getId(),
                            'sku' => $childProd->getSku(),
                            'title' => $childProd->getName(),
                            'created_at' => $created_at_child->format(\DateTime::ATOM),
                            'updated_at' => $updated_at_child->format(\DateTime::ATOM),
                            'inventory_quantity' => $stock->getQty(),
                            'price' => (float)$childProd->getPrice(),
                            'sale_price_with_tax' => $this->getFinalPrice($childProd)
                        ];
                    }
                }
            } else {
                $stock = $this->stockRegistry->getStockItem($row->getId());
                $variants[] = [
                    'inventory_quantity' => $stock->getQty(),
                    'price' => (float)$row->getPrice(),
                    'sale_price_with_tax' => $this->getFinalPrice($row)
                ];
            }
            $prod['variants'] = $variants;
            if ($vendorAttr) {
                if (!empty($row->getData($vendorAttr->getAttributeCode()))) {
                    $vendor = $vendorAttr->getFrontend()->getValue($row);
                    $prod['vendor'] = $vendor;
                } else {
                    $prod['vendor'] = null;
                }
            }
            if ($manufacturerAttr) {
                if (!empty($row->getData($manufacturerAttr->getAttributeCode()))) {
                    $manufacturer = $manufacturerAttr->getFrontend()->getValue($row);
                    $prod['manufacturer'] = $manufacturer;
                } else {
                    $prod['manufacturer'] = null;
                }
            }

            $productsArray[] = $this->dataOverride->product($row, $prod);
        }
        $object = new DataObject();
        $object->setProducts($productsArray);
        return $object;
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
     *
     * @return DataObject
     */
    public function getCustomers(
        $mage_store_id,
        $updated_at_min = null,
        $updated_at_max = null,
        $limit = null,
        $page = null,
        $since_id = null,
        $customer_id = null
    ) {
        $pageNumber = null;
        $pageSize = null;

        $customerData = $this->customerCollectionFactory->create();

        if ($customer_id !== null) {
            $customerData->addFieldToFilter('entity_id', $customer_id);
        }
        $customerData->addFieldToFilter('store_id', ['eq' => $mage_store_id]);
        if ($updated_at_min != null) {
            $customerData->addAttributeToFilter('updated_at', ['gt' => $this->convertTime($updated_at_min)]);
        }

        if ($updated_at_max != null) {
            $customerData->addAttributeToFilter('updated_at', ['lt' => $this->convertTime($updated_at_max)]);
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

        $posIdAttributeCode = $this->configHelper->getPOSAttributeCode();

        if (!empty($posIdAttributeCode)) {
            //make sure we get the POS id attribute
            $customerData->addAttributeToSelect([$posIdAttributeCode]);
        }

        $customerArray = [];
        $map = static::RESPONSE_MASK;

        $awRewardsIntegrate = false;
        if ($this->customerRewardPointsService) {
            if ($this->configHelper->isAheadworksRewardPointsEnabled()) {
                $awRewardsIntegrate = true;
            }
        }
        /**
         * @var CustomerInterface $customer
         */
        foreach ($customerData as $customer) {
            $customers = [];
            $mappedCustomer = $customer->getData();

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
            $customers['default_address'] = $this->dataHelper->getCustomerAddresses($customer);
            $group = $this->groupRepository->getById($customer->getGroupId());
            $customers['groups'][] = [
                'id' => $group->getId(),
                'name' => $group->getCode(),
            ];

            $customers['pos_id'] = !empty($posIdAttributeCode) && isset($mappedCustomer[$posIdAttributeCode]) ?
                $mappedCustomer[$posIdAttributeCode] : null;
            $customers['accepts_marketing'] = $this->checkSubscriber(
                $customer->getEmail(),
                $customer->getId(),
                $this->storeManager->getStore($mage_store_id)->getWebsiteId()
            );
            if ($awRewardsIntegrate) {
                $customers['rewards_points'] = $this->customerRewardPointsService->getCustomerRewardPointsBalance($customer->getId());
            }
            $customerArray[] = $this->dataOverride->customer($customer, $customers);
        }
        $object = new DataObject();
        $object->setCustomers($customerArray);

        return $object;
    }

    /**
     * @param Order\Address $customerAddresses
     * @return array|null
     */
    protected function getAddressData($customerAddresses)
    {
        $addressData = null;
        if ($customerAddresses) {
            $countryCode = $customerAddresses->getCountryId();
            $addressData = [
                'first_name' => $customerAddresses->getFirstname(),
                'last_name' => $customerAddresses->getLastname(),
                'city' => $customerAddresses->getCity(),
                'street' => implode(PHP_EOL, $customerAddresses->getStreet()),
                'country_code' => $countryCode,
                'country' => $customerAddresses->getCountry(),
                'zip' => $customerAddresses->getPostcode(),
                'phone' => $customerAddresses->getTelephone(),
                'region' => $customerAddresses->getRegionCode()
            ];
        }

        return $addressData;
    }

    public function mapCustomer($customerId, $mage_store_id)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        $customers = [];
        $mappedCustomer = $customer->getData();
        $mappedCustomer['id'] = $customerId;

        foreach (static::RESPONSE_MASK['customers'] as $element => $value) {
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
        $customers['pos_id'] = !empty($posIdAttributeCode) && $mappedCustomer[$posIdAttributeCode] ?
            $mappedCustomer[$posIdAttributeCode] : null;
        $customers['accepts_marketing'] = $this->checkSubscriber(
            $customer->getEmail(),
            $customerId,
            $this->storeManager->getStore($mage_store_id)->getWebsiteId()
        );
        $customers['default_address'] = $this->dataHelper->getCustomerAddresses($customer);

        return $this->dataOverride->customer($customer, $customers);
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
     *
     * @return DataObject
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
    ) {
        $pageNumber = null;
        $pageSize = null;

        /**
         * @var Order[] $orders
         */
        $orders = $this->orderCollectionFactory->create();

        $orders->addFieldToFilter('main_table.store_id', ['eq' => $mage_store_id]);
        if ($updated_at_min != null) {
            $orders->addAttributeToFilter('main_table.updated_at', ['gt' => $this->convertTime($updated_at_min)]);
        }

        if ($updated_at_max != null) {
            $orders->addAttributeToFilter('main_table.updated_at', ['lt' => $this->convertTime($updated_at_max)]);
        }

        if ($since_id != null) {
            $orders->addAttributeToFilter('main_table.entity_id', ['gt' => $since_id]);
        }

        if ($created_at_min != null) {
            $orders->addAttributeToFilter('main_table.created_at', ['gt' => $this->convertTime($created_at_min)]);
        }

        if ($created_at_max != null) {
            $orders->addAttributeToFilter('main_table.created_at', ['lt' => $this->convertTime($created_at_max)]);
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

        $map = static::RESPONSE_MASK;
        $ordersArray = [];
        foreach ($orders as $order) {
            $ord = [];
            $orderDetails = $order->getData();
            foreach ($map['orders'] as $element => $value) {
                if (!is_array($value)) {
                    if (array_key_exists($value, $orderDetails)) {
                        $ord[$element] = $orderDetails[$value];
                    }
                }
            }
            $ord['id'] = empty($order->getOriginalIncrementId()) ? $order->getIncrementId() : $order->getOriginalIncrementId();
            if ($order->getCustomerId()) {
                $ord['customer'] = $this->mapCustomer($order->getCustomerId(), $mage_store_id);
            } else {
                $billingAddress = $this->configHelper->getCustomerAddressType() === ConfigHelper::CUSTOMER_ADDRESS_SHIPPING ?
                    $order->getBillingAddress() : $order->getShippingAddress();

                $ord['customer']['email'] = $billingAddress->getEmail();
                $ord['customer']['accepts_marketing'] = $this->checkSubscriber(
                    $billingAddress->getEmail(),
                    null,
                    $this->storeManager->getStore($mage_store_id)->getWebsiteId()
                );
                $ord['customer']['guest'] = true;
                $ord['customer']['first_name'] = $billingAddress->getFirstname();
                $ord['customer']['last_name'] = $billingAddress->getLastname();
                $ord['customer']['title'] = $billingAddress->getPrefix();
                $ord['customer']['default_address'] = $this->getAddressData($billingAddress);
            }
            $ord['line_items'] = [];
            /**
             * @var Order\Item[] $items
             */
            $items = $order->getAllItems();
            foreach ($items as $item) {
                if ($item->getProductType() === Configurable::TYPE_CODE) {
                    continue;
                }
                $newItem = [];
                $itemElement = $item->getData();
                foreach ($map['orders']['line_items'] as $element => $value) {
                    if (!is_array($value)) {
                        if (array_key_exists($value, $itemElement)) {
                            $newItem[$element] = $itemElement[$value];
                        }
                    }
                }

                $parentItem = $item->getParentItem();
                if ($parentItem && $parentItem->getProductType() == Configurable::TYPE_CODE) {
                    $price = (float)$parentItem->getPrice();
                    $qty = (float)$parentItem->getQtyOrdered();
                    $totalTax = (float)$parentItem->getTaxAmount();
                    $total_with_tax = (float)$parentItem->getRowTotalInclTax();
                } else {
                    $price = (float)$item->getPrice();
                    $qty = (float)$item->getQtyOrdered();
                    $totalTax = (float)$item->getTaxAmount();
                    $total_with_tax = (float)$item->getRowTotalInclTax();
                }

                $newItem['price'] = $price;
                $newItem['quantity'] = $qty;
                if ($totalTax > 0 && $qty > 0) {
                    $newItem['tax_amount'] = ($totalTax / $qty);
                }
                $newItem['line_total_incl_tax'] = $total_with_tax;
                $newItem['line_total_tax'] = $totalTax;
                $ord['line_items'][] = $newItem;
            }
            $ord['status']= $this->getStoreOrderStatusesByCode($orderDetails['status']);
            $ord['state']= $order->getState();
            $ordersArray[]= $this->dataOverride->order($order, $ord);
        }

        $object = new DataObject();
        $object->setOrders($ordersArray);

        return $object;
    }
    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     *
     * @return DataObject
     */
    public function getProductsCount($mage_store_id)
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('entity_id');
        $collection->addStoreFilter($mage_store_id);

        $object = new DataObject();
        $object->setCount(count($collection));

        return $object;
    }

    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     * @return DataObject
     */
    public function getCustomersCount($mage_store_id)
    {
        $customerData = $this->customerCollectionFactory->create();
        $customerData->addFieldToFilter('store_id', ['eq' => $mage_store_id]);

        $object = new DataObject();
        $object->setCount(count($customerData));

        return $object;
    }
    /**
     * Get All customers from catalog
     *
     * @param int|null $mage_store_id
     * @return DataObject
     */
    public function getOrdersCount($mage_store_id)
    {
        $orders = $this->orderCollectionFactory->create();
        $orders->addFieldToFilter('store_id', ['eq' => $mage_store_id]);

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
    ) {
        $pageNumber = null;
        $pageSize = null;

        /**
         * @var Quote[] $quotes
         */
        $quotes = $this->quoteFactory->create()->getCollection();
        $quotes->addFieldToFilter('is_active', 1);
        $quotes->addFieldToFilter('customer_email', ['neq' => null]);

        if ($mage_store_id != null) {
            $quotes->addFieldToFilter('store_id', ['eq' => $mage_store_id]);
        }

        if ($updated_at_min != null) {
            $quotes->addFieldToFilter('main_table.updated_at', ['gt' => $this->convertTime($updated_at_min)]);
        }

        if ($updated_at_max != null) {
            $quotes->addFieldToFilter('main_table.updated_at', ['lt' => $this->convertTime($updated_at_max)]);
        }

        if ($since_id != null) {
            $quotes->addFieldToFilter('main_table.entity_id', ['gt' => $since_id]);
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
            $quotes->setPageSize($pageSize)->setCurPage($pageNumber);
        }

        if ($quote_id !== null) {
            $quotes->addFieldToFilter('entity_id', $quote_id);
        }
        $map = static::RESPONSE_MASK;

        $quoteCartArray = [];
        foreach ($quotes as $quote) {
            $quoteData = $quote->getData();
            $quoteArray = [];
            foreach ($map['carts'] as $element => $value) {
                if (!is_array($value)) {
                    if (array_key_exists($value, $quoteData)) {
                        $quoteArray[$element] = $quoteData[$value];
                    }
                }
            }

            $subtotal = $quote->getSubtotal();
            $subtotal_with_discount = $quote->getSubtotalWithDiscount();
            $quoteArray['order_discount'] = $subtotal-$subtotal_with_discount;

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
            $quoteArray['total_shipping'] = empty($defaultShipping['shipping_amount']) ? 0 : (float)$defaultShipping['shipping_amount'];
            $itemsCollection = $quote->getItemsCollection();
            if ($quote->getCustomerId()) {
                $customer = $this->mapCustomer($quote->getCustomerId(), $mage_store_id);
                $quoteArray['customer'] = $customer;
            } else {
                $quoteArray['customer'] = null;
            }

            $itemArray = [];
            $cartTotalTax = 0;
            foreach ($itemsCollection as $item) {
                if ($item->getProductType() === Configurable::TYPE_CODE) {
                    continue;
                }
                $itemsData = $item->getData();
                $itemData = [];
                foreach ($map['carts']['line_items'] as $element => $value) {
                    if (!is_array($value)) {
                        if (array_key_exists($value, $itemsData)) {
                            $itemData[$element] = $itemsData[$value];
                        }
                    }
                }

                $parentItem = $item->getParentItem();
                if ($parentItem && $parentItem->getProductType() == Configurable::TYPE_CODE) {
                    $itemData['price'] = $parentItem->getPrice();
                    $qty = (float)$parentItem->getQty();
                    $totalTax = empty($parentItem['tax_amount']) ? 0 : (float)$parentItem['tax_amount'];
                } else {
                    $qty = (float)$item->getQty();
                    $totalTax = empty($itemData['tax_amount']) ? 0 : (float)$itemData['tax_amount'];
                }
                $cartTotalTax += $totalTax;
                $itemData['quantity'] = $qty;

                if (!empty($totalTax) && $qty > 0) {
                    $taxAmount = $totalTax / $qty;
                } else {
                    $taxAmount = 0;
                }
                $itemData['tax_amount'] = $taxAmount;
                $itemArray[] = $itemData;
            }
            $quoteArray['total_tax'] = $cartTotalTax;
            $quoteArray['line_items'] = $itemArray;
            $quoteArray['abandoned_checkout_url'] = $this->recoveryHelper->getCartRecoveryURL($quoteData['entity_id'], $mage_store_id);
            $quoteCartArray[] = $this->dataOverride->cart($quote, $quoteArray);
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
        /** @var StoreManagerInterface $manager */
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
        $configData = [
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
        ];

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
        foreach ($orderStatuses as $status) {
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

    /**
     * Create coupon
     *
     * @param int     $ruleId
     * @param string $couponCode
     * @param string $expiration
     *
     * @return array $response
     */
    public function createCoupon($ruleId, $couponCode, $expiration = null)
    {
        $errorMessage = null;
        try {
            $rule = $this->ruleRepository->getById($ruleId);

            $coupon = $this->couponFactory->create();
            $coupon->setRule($rule)
                ->setIsPrimary(false)
                ->setCode($couponCode)
                ->setAddedByRemarkety(1)
                ->setUsageLimit($rule->getUsesPerCoupon())
                ->setUsagePerCustomer($rule->getUsesPerCustomer())
                ->setCreatedAt(date("Y-m-d H:i:s"))
                ->setType(Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED);

            if ($expiration != null) {
                $coupon->setExpirationDate($this->convertTime($expiration));
            } else {
                $coupon->setExpirationDate($rule->getToDate());
            }
            $coupon->save();

            $status = true;
        } catch (LocalizedException $e) {
            $status = false;
            $errorMessage = $e->getMessage();
        }

        $response = [
            'response' => [
                'status' => $status,
                'error'  => [
                    'message' => $errorMessage
                ]
            ]
        ];

        return $response;
    }

    /**
     * @param int|null $mage_store_id
     * @param string $configName
     * @param string $scope
     * @return string
     */
    public function getConfig($mage_store_id, $configName, $scope)
    {
        $store_id = 0;
        if ($scope == 'stores') {
            $store_id = $mage_store_id;
        } else {
            $scope = 'default';
        }
        return $this->scopeConfig->getValue('remarkety/mgconnector/' . $configName, $scope, $store_id);
    }

    /**
     * @param int|null $mage_store_id
     * @param string $configName
     * @param string $scope
     * @param string $newValue
     * @return string
     */
    public function setConfig($mage_store_id, $configName, $scope, $newValue)
    {
        $store_id = 0;
        if ($scope == 'stores') {
            $store_id = $mage_store_id;
        } else {
            $scope = 'default';
        }
        $this->resourceConfig->saveConfig(
            'remarkety/mgconnector/' . $configName,
            $newValue,
            $scope,
            $store_id
        );

        $this->cacheTypeList->cleanType('config');
        return 1;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '2.4.9';
    }

    /**
     * @param int $mage_store_id
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $minId
     * @param int|null $maxId
     * @return \Remarkety\Mgconnector\Api\Data\QueueCollectionInterface
     */
    public function getQueueItems($mage_store_id, $limit = null, $page = null, $minId = null, $maxId = null)
    {
        $sel = $this->queueCollection
            ->getSelect();
        $sel->where('store_id', $mage_store_id)
            ->order('queue_id asc');

        if (empty($limit) || !is_numeric($limit)) {
            $limit = 10;
        }

        if (empty($limit) || !is_numeric($limit)) {
            $page = 0;
        }

        if (is_numeric($limit) && is_numeric($page)) {
            $page++;
            $sel->limitPage($page, $limit);
        }

        if (is_numeric($minId)) {
            $sel->where('queue_id >= ' . $minId);
        }

        if (is_numeric($maxId)) {
            $sel->where('queue_id <= ' . $maxId);
        }

        $object = new \Remarkety\Mgconnector\Model\Api\Data\QueueCollection();
        $data = $this->queueCollection->toArray();
        $object->setQueueItems($data['items']);

        return $object;
    }

    /**
     * @param int $mage_store_id
     * @param int|null $minId
     * @param int|null $maxId
     * @return array
     */
    public function deleteQueueItems($mage_store_id, $minId = null, $maxId = null)
    {
        $sel = $this->queueCollection
            ->getSelect();
        $sel->where('store_id = ' . $mage_store_id)
            ->order('queue_id asc');

        if (is_numeric($minId)) {
            $sel->where('queue_id >= ' . $minId);
        }

        if (is_numeric($maxId)) {
            $sel->where('queue_id <= ' . $maxId);
        }
        $toDelete = $this->queueCollection->count();
        $itemsDeleted = 0;
        foreach ($this->queueCollection as $item) {
            try {
                $this->queueRepository->delete($item);
                $itemsDeleted++;
            } catch (\Exception $ex) {
            }
        }
        $ret = [
            'response' => [
                'totalMatching' => $toDelete,
                'totalDeleted' => $itemsDeleted
            ]
        ];
        return $ret;
    }

    /**
     * @param int $mage_store_id
     * @param int|null $limit
     * @param int|null $page
     * @param int|null $minId
     * @param int|null $maxId
     *
     * @return array
     */
    public function retryQueueItems($mage_store_id, $limit = null, $page = null, $minId = null, $maxId = null)
    {
        $sel = $this->queueCollection
            ->getSelect();
        $sel->where('store_id = ' . $mage_store_id)
            ->order('queue_id asc');

        if (is_numeric($minId)) {
            $sel->where('queue_id >= ' . $minId);
        }

        if (is_numeric($maxId)) {
            $sel->where('queue_id <= ' . $maxId);
        }

        if (empty($limit) || !is_numeric($limit)) {
            $limit = 10;
        }

        if (empty($limit) || !is_numeric($limit)) {
            $page = 0;
        }

        if (is_numeric($limit) && is_numeric($page)) {
            $page++;
            $sel->limitPage($page, $limit);
        }

        $itemsSent = 0;
        /**
         * @var $item QueueInterface
         */
        foreach ($this->queueCollection as $item) {
            try {
                if ($this->eventMethods->makeRequest(
                    $item->getEventType(),
                    json_decode($item->getPayload(), true),
                    $item->getStoreId(),
                    $item->getAttempts(),
                    $item->getQueueId()
                )) {
                    $itemsSent++;
                    $this->queueRepository->delete($item);
                }
            } catch (\Exception $ex) {
            }
        }

        return [
            'response' => [
                'totalMatching' => $this->queueCollection->count(),
                'sentSuccessfully' => $itemsSent
            ]
        ];
    }

    public function unsubscribe($mage_store_id, $email)
    {
        $result = new DataObject();
        $result->setStatus('success');

        if (empty($mage_store_id)) {
            $result->setStatus('error');
            $result->setMessage('mage_store_id is required parameter');
        }

        if (empty($email)) {
            $result->setStatus('error');
            $result->setMessage('email is required parameter');
        }

        if ($result->getStatus() == 'error') {
            throw new Exception(__($result->getMessage()), 400);
        }

        $email = strtolower(trim($email));
        $subscriber = $this->subscriberFactory->create()->loadByEmail($email);
        $found_email = strtolower(trim($subscriber->getEmail()));
        if ($found_email == $email) {
            $subscriber->unsubscribe();
            $result->setMessage('Customer unsubscribed successfuly');
        } else {
            $result->setMessage('Newsletter subscriber does not exists');
        }

        return $result;
    }

    private function getFinalPrice($row)
    {
        $price = $row->getFinalPrice();
        $price_info = 0;
        if ($this->configHelper->getWithFixedProductTax()) {
            $price_info = $row->getPriceInfo()->getPrice('final_price')->getAmount()->getTotalAdjustmentAmount();
        }

        return (float)$price + (float)$price_info;
    }

    private function convertTime($string)
    {
        $timestamp = strtotime($string);

        return date('Y-m-d H:i:s', $timestamp);
    }
}
