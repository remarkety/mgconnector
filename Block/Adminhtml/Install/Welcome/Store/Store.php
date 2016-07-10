<?php

namespace Remarkety\Mgconnector\Block\Adminhtml\Install\Welcome\Store;

//use \Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManager;

class Store extends \Magento\Framework\View\Element\Template
{
    protected $_configResource;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        StoreManager $storeManager,
        ScopeConfigInterface $scopeConfig
    ){
        $this->storeManager = $storeManager;
        $this->_configResource = $scopeConfig;
        parent::__construct($context);
    }
    public function _toHtml(){
        $this->setTemplate('mgconnector/install/welcome/store.phtml');
        return parent::_toHtml();

    }

    public function getStoresStatus()
    {
        $stores = array();

        foreach ($this->storeManager->getWebsites() as $_website) {
            $stores[$_website->getCode()] = array(
                'name' => $_website->getName(),
                'id' => $_website->getWebsiteId(),
            );

            foreach ($_website->getGroups() as $_group) {
                $stores[$_website->getCode()]['store_groups'][$_group->getGroupId()] = [
                    'name' => $_group->getName(),
                    'id' => $_group->getGroupId(),
                ];

                foreach ($_group->getStores() as $_store) {
                    $isInstalled = $this->_configResource->getValue(\Remarkety\Mgconnector\Model\Install::XPATH_INSTALLED, \Remarkety\Mgconnector\Model\Install::STORE_SCOPE, $_store->getStoreId());
                    $stores[$_website->getCode()]['store_groups'][$_group->getGroupId()]['store_views'][$_store->getStoreId()] = array(
                        'name' => $_store->getName(),
                        'id' => $_store->getStoreId(),
                        'isInstalled' => $isInstalled,
                    );
                }
            }
        }
        return $stores;
    }
}