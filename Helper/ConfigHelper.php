<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 4/27/17
 * Time: 11:32 AM
 */

namespace Remarkety\Mgconnector\Helper;
use Magento\Framework\App\Cache\TypeList;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use \Magento\Config\Model\ResourceModel\Config;

class ConfigHelper
{
    const RM_STORE_ID = 'remarkety/mgconnector/public_storeId';
    const WEBHOOKS_ENABLED = 'remarkety/mgconnector/webhooks';
    const PRODUCT_WEBHOOKS_DISABLED = 'remarkety/mgconnector/product_webhooks_disable';
    const FORCE_ASYNC_WEBHOOKS = 'remarkety/mgconnector/forceasyncwebhooks';

    protected $_activeStore;
    protected $_scopeConfig;
    protected $configResource;
    protected $cacheTypeList;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManager $sManager,
        Config $configResource,
        TypeList $cacheTypeList
    ){
        $this->_scopeConfig = $scopeConfig;
        $this->_activeStore = $sManager->getStore();
        $this->configResource = $configResource;
        $this->cacheTypeList = $cacheTypeList;
    }

    public function isStoreInstalled($storeId){
        $installed = $this->_scopeConfig->getValue(\Remarkety\Mgconnector\Model\Install::XPATH_INSTALLED, \Remarkety\Mgconnector\Model\Install::STORE_SCOPE, $storeId);
        return !empty($installed);
    }

    public function getRemarketyPublicId($store = null)
    {
        $store = is_null($store) ? $this->_activeStore : $store;
        $store_id = is_numeric($store) ? $store : $store->getId();
        $id = $this->_scopeConfig->getValue(self::RM_STORE_ID, ScopeInterface::SCOPE_STORES, $store_id);
        return (empty($id) || is_null($id)) ? false : $id;
    }

    public function isWebhooksGloballyEnabled(){
        $webhooksEnabled = $this->_scopeConfig->getValue(self::WEBHOOKS_ENABLED);
        if(is_null($webhooksEnabled) || !empty($webhooksEnabled)){
            return true;
        }
        return false;
    }

    public function shouldSendProductUpdates(){
        $webhooks = $this->_scopeConfig->getValue(self::PRODUCT_WEBHOOKS_DISABLED);
        if(empty($webhooks)){
            return true;
        }
        return false;
    }

    public function forceAsyncWebhooks(){
        $async = $this->_scopeConfig->getValue(self::FORCE_ASYNC_WEBHOOKS);
        if(!empty($async)){
            return true;
        }
        return false;
    }

    /**
     * @param bool $enabled
     */
    public function setWebhooksGloballStatus($enabled){
        $this->configResource->saveConfig(
            self::WEBHOOKS_ENABLED,
            $enabled ? 1 : 0,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $this->cacheTypeList->cleanType('config');
    }
}
