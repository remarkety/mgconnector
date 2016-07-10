<?php
namespace Remarkety\Mgconnector\Model;

use \Magento\Config\Model\ResourceModel\Config;
use Magento\Store\Model\StoreManager;
use \Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;

Class Webtracking extends \Magento\Framework\Model\AbstractModel
{
    const RM_STORE_ID = 'remarkety/mgconnector/public_storeId';
    const STORE_SCOPE = 'stores';

    protected $_activeStore;
    protected $_configResource;
    protected $_configRead;
    public function __construct(Config $resourceConfig, StoreManager $sManager, ScopeConfigInterface $scopeConfig){
        $this->_configResource = $resourceConfig;
        $this->_activeStore = $sManager->getStore();
        $this->_configRead = $scopeConfig;
    }

    public function getRemarketyPublicId($store = null)
    {
        $store = is_null($store) ? $this->_activeStore : $store;
        $store_id = is_numeric($store) ? $store : $store->getId();
        $id = $this->_configRead->getValue(self::RM_STORE_ID, self::STORE_SCOPE, $store_id);
        return (empty($id) || is_null($id)) ? false : $id;
    }

    public function setRemarketyPublicId($store, $newId)
    {
        $store_id = is_numeric($store) ? $store : $store->getId();
        $this->_configResource->saveConfig(
            self::RM_STORE_ID,
            $newId,
            self::STORE_SCOPE,
            $store_id
        );
    }
}