<?php
declare(strict_types=1);

namespace Remarkety\Mgconnector\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Exception\NoSuchEntityException;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Helper\Data;

class ProductDataResolver
{
    private const PARENT_PRODUCT_SOURCE = 'parent';

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var ProductInterface
     */
    private $parentProduct = null;

    /**
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper,
        ProductRepositoryInterface $productRepository,
        Data $dataHelper
    ) {
        $this->configHelper = $configHelper;
        $this->productRepository = $productRepository;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param string|bool $parentId
     * @param $childProduct
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getTitle($parentId, $childProduct): string
    {
        $titleSource = $this->configHelper->getProductTitleSource();

        if ($titleSource === static::PARENT_PRODUCT_SOURCE && $parentId) {
            return $this->getParentProduct((int)$parentId)->getName();
        }

        return $childProduct->getName();
    }

    /**
     * @param string|bool $parentId
     * @param $childProduct
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getImages($parentId, $childProduct): array
    {
        $imagesSource = $this->configHelper->getProductImagesSource();

        if ($imagesSource === static::PARENT_PRODUCT_SOURCE && $parentId) {
            $parentProduct = $this->getParentProduct((int)$parentId);
            return $this->dataHelper->getMediaGalleryImages($parentProduct);
        }

        return $this->dataHelper->getMediaGalleryImages($childProduct);
    }

    /**
     * @param string|bool $parentId
     * @param $childProduct
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getUrl($parentId, $childProduct): string
    {
        if ($childProduct->getTypeId() === Type::TYPE_SIMPLE && $parentId) {
            return $this->getParentProduct((int) $parentId)->getProductUrl();
        }

        return $childProduct->getProductUrl();
    }

    /**
     * @param int $parentId
     *
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getParentProduct(int $parentId): ProductInterface
    {
        if (is_null($this->parentProduct) || $this->parentProduct->getId() !== $parentId) {
            $this->parentProduct = $this->productRepository->getById($parentId);
        }

        return $this->parentProduct;
    }
}
