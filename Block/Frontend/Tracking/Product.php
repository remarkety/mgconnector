<?php
namespace Remarkety\Mgconnector\Block\Frontend\Tracking;

use \Magento\Framework\Registry;
use Magento\Store\Model\StoreManager;
use Magento\Framework\View\Element\Template\Context;
use \Remarkety\Mgconnector\Model\Webtracking;
use \Magento\Customer\Model\Session;
use Magento\Catalog\Model\Product as MageProduct;
class Product extends Base {
    
    protected $_activeProduct;
    public function __construct(
        Context $context,
        array $data = [],
        StoreManager $sManager,
        Webtracking $webtracking,
        Session $session,
        Registry $registry)
    {
        parent::__construct($context, $data, $sManager, $webtracking, $session);
        $this->_activeProduct = $registry->registry('current_product');
    }

    /**
     * @return MageProduct
     */
    public function getActiveProduct(){
        return $this->_activeProduct;
    }
    public function getCategoryNames(){
        $cat = $this->getActiveProduct()->getCategory();
        if($cat){
            return $cat->getName();
        }
        return '';
    }
    public function getCategoryIds(){
        $cat = $this->getActiveProduct()->getCategory();
        if($cat){
            return $cat->getId();
        }
        return '';
    }
    public function categoriesData(){

    }
}