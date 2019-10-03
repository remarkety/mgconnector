<?php


namespace Remarkety\Mgconnector\Block\Frontend\Tracking;


use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\CatalogSearch\Block\Result;
use Magento\CatalogSearch\Helper\Data;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use \Magento\Framework\Registry;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManager;
use Magento\Framework\View\Element\Template\Context;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use \Remarkety\Mgconnector\Model\Webtracking;
use \Magento\Customer\Model\Session;

class Search extends Result
{
    private $term;
    private $media_path;
    private $config_helper;

    public function __construct(
        Context $context,
        LayerResolver $layerResolver,
        Data $catalogSearchData,
        QueryFactory $queryFactory,
        array $data = [],
        ConfigHelper $config_helper
    ) {
        parent::__construct($context, $layerResolver, $catalogSearchData, $queryFactory, $data);
        $this->config_helper = $config_helper;
    }

    public function isEventSearchViewActivated() {
        return $this->config_helper->isEventSearchViewEnabled();
    }

    public function getQueryTerm() {
        if (!$this->term) {
            $this->term = $this->catalogSearchData->getEscapedQueryText();
        }

        return $this->term;
    }

    public function getResultProducts() {
        $data = [];
        $size = 3;

        foreach ($this->getProducts() as $entity) {
            if ($size > 0) {
                $size--;

                if($entity->getTypeId() == Configurable::TYPE_CODE){
                    $full_price = $entity->getMinimalPrice();
                    $price = $entity->getMinimalPrice();
                } else {
                    $full_price = $entity->getFinalPrice();
                    $price = $entity->getPrice();
                }

                $data[] = [
                    'product_id' => $entity->getId(),
                    'title'      => $entity->getName(),
                    'image'      => $this->getMediaPath($entity) . $entity->getThumbnail(),
                    'full_price' => floatval($full_price),
                    'price'      => floatval($price),
                    'url'        => $entity->getProductUrl()
                ];
            } else {
                break;
            }
        }

        return $data;
    }

    private function getProducts() {
        $layout = $this->getLayout()->getBlock('search_result_list');
        $product_collection = $layout->getLoadedProductCollection();

        return $product_collection;
    }

    private function getMediaPath($entity) {
        if (!$this->media_path) {
            $this->media_path = $entity->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        }

        return $this->media_path;
    }
}
