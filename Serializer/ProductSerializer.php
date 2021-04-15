<?php

namespace Remarkety\Mgconnector\Serializer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Store\Model\StoreManagerInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Helper\Data;
use Remarkety\Mgconnector\Helper\DataOverride;
use Remarkety\Mgconnector\Resolver\ProductDataResolver;
use Magento\Catalog\Model\Product\Type;

class ProductSerializer
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataOverride
     */
    private $dataOverride;

    /**
     * @var Configurable
     */
    private $catalogProductTypeConfigurable;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var ProductDataResolver
     */
    private $productDataResolver;

    /**
     * @param ProductRepository $productRepository
     * @param Data $dataHelper
     * @param StockRegistryInterface $stockRegistry
     * @param StoreManagerInterface $storeManager
     * @param DataOverride $dataOverride
     * @param ConfigHelper $configHelper
     * @param ProductDataResolver $productDataResolver
     */
    public function __construct(
        Configurable $catalogProductTypeConfigurable,
        ProductRepository $productRepository,
        Data $dataHelper,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
        DataOverride $dataOverride,
        ConfigHelper $configHelper,
        ProductDataResolver $productDataResolver
    ) {
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->productRepository = $productRepository;
        $this->dataHelper = $dataHelper;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->dataOverride = $dataOverride;
        $this->configHelper = $configHelper;
        $this->productDataResolver = $productDataResolver;
    }

    public function loadProduct($product_id, $store_id = null)
    {
        return $this->productRepository->getById($product_id, false, $store_id);
    }

    public function serialize(ProductInterface $product, $storeId)
    {
        $parentId = null;
        $parentProduct = null;

        if ($product->getTypeId() === Type::TYPE_SIMPLE) {
            $parentId = $this->dataHelper->getParentId($product->getId());
            if (!empty($parentId)) {
                $parentProduct = $this->loadProduct($parentId, $storeId);
            }
        }

        $store = $this->storeManager->getStore($storeId);
        $product->setStoreId($storeId);
        $product->setWebsiteId($store->getWebsiteId());
        $product->setCustomerGroupId(0);

        //makes sure the final price is re-calculated based on the current store
        $product->setFinalPrice(null);

        $created_at = new \DateTime($product->getCreatedAt());
        $updated_at = new \DateTime($product->getUpdatedAt());

        $enabled = $product->getStatus() == Status::STATUS_ENABLED && $product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE;

        $variants = [];

        if (!empty($parentProduct)) {
            $parentProduct->setStoreId($storeId);
            $categoryIds = $parentProduct->getCategoryIds();

            //contain only the current product stock level
            $stock = $this->stockRegistry->getStockItem($product->getId());
            $variants[] = [
                'inventory_quantity' => $stock->getQty(),
                'price' => (float)$product->getPrice(),
                'salePrice' => $this->getFinalPrice($product)
            ];

        } else {
            $categoryIds = $product->getCategoryIds();

            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                //configurable products sends variants
                $childrenIdsGroups = $this->catalogProductTypeConfigurable->getChildrenIds($product->getId());
                if (isset($childrenIdsGroups[0])) {
                    $childrenIds = $childrenIdsGroups[0];
                    foreach ($childrenIds as $childId) {
                        $childProd = $this->loadProduct($childId, $storeId);
                        $childProd->setStoreId($storeId);
                        $childProd->setWebsiteId($store->getWebsiteId());
                        $childProd->setCustomerGroupId(0);

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
                            'salePrice' => $this->getFinalPrice($childProd)
                        ];
                    }
                }
            } else {
                $stock = $this->stockRegistry->getStockItem($product->getId());
                $variants[] = [
                    'inventory_quantity' => $stock->getQty(),
                    'price' => (float)$product->getPrice(),
                    'salePrice' => $this->getFinalPrice($product)
                ];
            }

        }

        $categories = [];
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $categories[] = $this->dataHelper->getCategory($categoryId, $storeId);
            }
        }

        $vendor = null;
        $manufacturer = null;

        $vendorAttr = $product->getResource()->getAttribute('vendor');
        if (!$vendorAttr) {
            $vendorAttr = $product->getResource()->getAttribute('brand');
        }
        $manufacturerAttr = $product->getResource()->getAttribute('manufacturer');
        if ($manufacturerAttr) {
            if (!empty($product->getData($manufacturerAttr->getAttributeCode()))) {
                $manufacturer = $manufacturerAttr->getFrontend()->getValue($product);
            }
        }
        if ($vendorAttr) {
            if (!empty($product->getData($vendorAttr->getAttributeCode()))) {
                $vendor = $vendorAttr->getFrontend()->getValue($product);
            }
        }
        $data = [
            'id' => $product->getId(),
            'sku' => $product->getSku(),
            'title' => $this->productDataResolver->getTitle($parentId, $product),
            'categories' => $categories,
            'created_at' => $created_at->format(\DateTime::ATOM),
            'updated_at' => $updated_at->format(\DateTime::ATOM),
            'images' => $this->productDataResolver->getImages($parentId, $product),
            'enabled' => $enabled,
            'price' => (float)$product->getPrice(),
            'salePrice' => $this->getFinalPrice($product),
            'url' => $this->productDataResolver->getUrl($parentId, $product),
            'variants' => $variants,
            'options' => [],
            'vendor' => $vendor,
            'manufacturer' => $manufacturer
        ];
        if (!empty($parentId)) {
            $data['parent_id'] = $parentId;
        }

        return $this->dataOverride->product($product, $data);
    }

    /**
     * @param $row
     *
     * @return float
     */
    private function getFinalPrice($row): float
    {
        $price = $row->getFinalPrice();
        $price_info = 0;
        if ($this->configHelper->getWithFixedProductTax()) {
            $price_info = $row->getPriceInfo()->getPrice('final_price')->getAmount()->getTotalAdjustmentAmount();
        }

        return (float)$price + (float)$price_info;
    }
}
