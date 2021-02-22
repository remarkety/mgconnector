<?php
namespace Remarkety\Mgconnector\Block\Frontend\Tracking;

use \Magento\Framework\Registry;
use Magento\Store\Model\StoreManager;
use Magento\Framework\View\Element\Template\Context;
use Remarkety\Mgconnector\Helper\Data;
use \Remarkety\Mgconnector\Model\Webtracking;
use \Magento\Customer\Model\Session;
use Magento\Catalog\Model\Product as ProductModel;

class Product extends Base
{

    /**
     * @var ProductModel
     */
    protected $activeProduct;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @param Context $context
     * @param array $data
     * @param StoreManager $sManager
     * @param Webtracking $webtracking
     * @param Session $session
     * @param Registry $registry
     * @param Data $dataHelper
     */
    public function __construct(
        Context $context,
        array $data,
        StoreManager $sManager,
        Webtracking $webtracking,
        Session $session,
        Registry $registry,
        Data $dataHelper
    ) {
        parent::__construct($context, $data, $sManager, $webtracking, $session);

        $this->activeProduct = $registry->registry('current_product');
        $this->dataHelper = $dataHelper;
    }

    /**
     * @return ProductModel
     */
    public function getActiveProduct(): ProductModel
    {
        return $this->activeProduct;
    }

    /**
     * @return string
     */
    public function getCategoryNames(): string
    {
        $categoryIds = $this->getActiveProduct()->getCategoryIds();
        $result = [];

        foreach ($categoryIds as $categoryId) {
            $category = $this->dataHelper->getCategory($categoryId);
            $result[] = $category['name'];
        }

        return json_encode($result);
    }

    /**
     * @return string
     */
    public function getCategoryIds(): string
    {
        return json_encode($this->getActiveProduct()->getCategoryIds());
    }
}
