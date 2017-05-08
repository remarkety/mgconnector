<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 4/30/17
 * Time: 1:25 PM
 */

namespace Remarkety\Mgconnector\Serializer;


use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Url;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Remarkety\Mgconnector\Helper\Data;

class ProductSerializer
{
    protected $categoryFactory;
    protected $catalogProductTypeConfigurable;
    protected $productRepository;
    protected $dataHelper;
    protected $urlModel;
    protected $stockRegistry;
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        ProductRepository $productRepository,
        Data $dataHelper,
        Url $urlModel,
        StockRegistryInterface $stockRegistry
    )
    {
        $this->categoryFactory = $categoryFactory;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->productRepository = $productRepository;
        $this->dataHelper = $dataHelper;
        $this->urlModel = $urlModel;
        $this->stockRegistry = $stockRegistry;
    }

    public function loadProduct($product_id){
        return $this->productRepository->getById($product_id);
    }

    public function serialize(ProductInterface $product, $storeId){

        $parent_id = null;
        if($product->getTypeId() == 'simple'){
            $parent_id = $this->dataHelper->getParentId($product->getId());
            if(!empty($parent_id)) {
                $parentProduct = $this->loadProduct($parent_id);
            }
        }

        $created_at = new \DateTime($product->getCreatedAt());
        $updated_at = new \DateTime($product->getUpdatedAt());

        $enabled = $product->getStatus() == Status::STATUS_ENABLED && $product->getVisibility() != Visibility::VISIBILITY_NOT_VISIBLE;

        $variants = [];

        if(!empty($parentProduct)){
            $parentProduct->setStoreId($storeId);
            $url = $parentProduct->getProductUrl(false);
            $images = $this->dataHelper->getMediaGalleryImages($parentProduct);
            $categoryIds = $parentProduct->getCategoryIds();

            //contain only the current product stock level
            $stock = $this->stockRegistry->getStockItem($product->getId());
            $variants[] = [
                'inventory_quantity' => $stock->getQty(),
                'price' => (float)$product->getPrice()
            ];

        } else {
            $product->setStoreId($storeId);
            $categoryIds = $product->getCategoryIds();
            $url = $product->getProductUrl(false);
            $images = $this->dataHelper->getMediaGalleryImages($product);

            if($product->getTypeId() == Configurable::TYPE_CODE){
                //configurable products sends variants
                $childrenIdsGroups = $this->catalogProductTypeConfigurable->getChildrenIds($product->getId());
                if(isset($childrenIdsGroups[0])) {
                    $childrenIds = $childrenIdsGroups[0];
                    foreach ($childrenIds as $childId) {
                        $childProd = $this->loadProduct($childId);
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
                            'price' => (float)$childProd->getPrice()
                        ];
                    }
                }
            } else {
                $stock = $this->stockRegistry->getStockItem($product->getId());
                $variants[] = [
                    'inventory_quantity' => $stock->getQty(),
                    'price' => (float)$product->getPrice()
                ];
            }

        }

        $categories = [];
        if(!empty($categoryIds)){
            foreach($categoryIds as $categoryId){
                $categories[] = $this->dataHelper->getCategory($categoryId);
            }
        }


        $options = [];

        $data = [
            'id' => $product->getId(),
            'sku' => $product->getSku(),
            'title' => $product->getName(),
            'categories' => $categories,
            'created_at' => $created_at->format(\DateTime::ATOM ),
            'updated_at' => $updated_at->format(\DateTime::ATOM ),
            'images' => $images,
            'enabled' => $enabled,
            'price' => (float)$product->getPrice(),
            'url' => $url,
            'variants' => $variants,
            'options' => $options
        ];
        if(!empty($parent_id)){
            $data['parent_id'] = $parent_id;
        }
        return $data;

        /**
         * $data = array(
        'id' => $product->getId(),
        'sku' => $product->getSku(),
        'title' => $rmCore->getProductName($product, $storeId),
        'body_html' => '',
        'categories' => $categories,
        'created_at' => $product->getCreatedAt(),
        'updated_at' => $product->getUpdatedAt(),
        'images' => $this->getProductImages($product),
        'enabled' => $enabled,
        'price' => $price,
        'special_price' => $special_price,
        'url' => $url,
        'parent_id' => $rmCore->getProductParentId($product),
        'variants' => array(
        array(
        'inventory_quantity' => $stocklevel,
        'price' => $price
        )
        )
        );
         */
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
}
