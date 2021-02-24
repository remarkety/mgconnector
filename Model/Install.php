<?php


namespace Remarkety\Mgconnector\Model;

use Magento\Framework\App\Cache\TypeList;
use \Magento\Framework\Model\Context;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\App\Config\Data as ConfigData;
use \Magento\Integration\Model\Integration;
use \Magento\Authorization\Model\Role;
use \Magento\Authorization\Model\Rules;
use \Magento\Store\Model\Store;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use \Remarkety\Mgconnector\Model\Request as MgconnectorRequest;
use \Remarkety\Mgconnector\Model\Webtracking;
use \Magento\Framework\UrlInterface;
use \Magento\Framework\Module\ModuleResource;
use \Magento\Config\Model\Config;
use \Magento\Customer\Model\Session;

class Install extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Upgrade mode code
     */
    const MODE_UPGRADE = 'upgrade';

    /**
     * Install create mode code
     */
    const MODE_INSTALL_CREATE = 'install_create';

    /**
     * Install login mode code
     */
    const MODE_INSTALL_LOGIN = 'install_login';

    /**
     * Completed mode code
     */
    const MODE_COMPLETE = 'complete';

    /**
     * Welcome mode code
     */
    const MODE_WELCOME = 'welcome';

    /**
     * Web service remarkety username
     */
    const WEB_SERVICE_USERNAME = 'remarkety';

    /**
     * Web service role name
     */
    const WEB_SERVICE_ROLE = 'remarkety';

    /**
     * Store code scope
     */
    const STORE_SCOPE = 'stores';

    /**
     * Key in config for installed flag
     */
    const XPATH_INSTALLED = 'remarkety/mgconnector/installed';

    /**
     * Key in config for remarkety public store id
     */
    const XPATH_PUBLIC_STORE_ID = 'remarkety/mgconnector/public_storeId';

    /**
     * Install data
     *
     * @var array
     */
    protected $_data = null;

    protected $_resourceConfig;

    protected $_messageManager;


    protected $_systemStore;

    protected $_integrationService;

    protected $_session;

    protected $_webtracking;

    protected $_cache;

    private $serialize;

    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        Integration $integration,
        Role $role,
        Rules $rules,
        \Magento\User\Model\User $user,
        Store $store,
        MgconnectorRequest $mgconnectorRequest,
        Webtracking $rmWebtracking,
        ModuleResource $moduleResource,
        Config $config,
        \Magento\Integration\Model\IntegrationService $integrationService,
        \Magento\Integration\Model\OauthService $oauthService,
        Session $session,
        TypeList $cache,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize
    ) {
        parent::__construct($context, $registry);
        $this->_cache = $cache;
        $this->context = $context;
        $this->scopeConfigInterface = $scopeConfig;
        $this->_resourceConfig = $resourceConfig;
        $this->integration = $integration;
        $this->role = $role;
        $this->rules = $rules;
        $this->user = $user;
        $this->store = $store;
        $this->mgconnectorRequest = $mgconnectorRequest;
        $this->moduleResource = $moduleResource;
        $this->config = $config;
        $this->_integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->_session = $session;
        $this->serialize = $serialize;
        $this->_webtracking = $rmWebtracking;
    }


    public function setData($key, $value = null)
    {
        $this->_data['mode'] = $key['mode'];
        $this->_data['email']= array_key_exists('email', $key) ? $key['email'] : null;
        $this->_data['first_name']= array_key_exists('first_name', $key) ? $key['first_name'] : null;
        $this->_data['last_name']= array_key_exists('last_name', $key) ? $key['last_name'] : null;
        $this->_data['phone']= array_key_exists('phone', $key) ? $key['phone'] : null;
        $this->_data['password']= array_key_exists('password', $key) ? $key['password'] : null;
        $this->_data['terms'] = array_key_exists('terms', $key) ? ($key['terms']== '1' ? 'true' : 'false') : null;
        $this->_data['store_id'] = array_key_exists('store_id', $key) ? $key['store_id'] : null;
        $this->_data['key'] = array_key_exists('key', $key) ? $key['key'] : null;
        $this->_data['http_user'] = array_key_exists('http_user', $key) ? $key['http_user'] : null;
        $this->_data['http_password'] = array_key_exists('http_password', $key) ? $key['http_password'] : null;

        return $this;
    }


    public function getData($key = '', $index = null)
    {
        return $this->_data;
    }

    protected function _webServiceConfiguration()
    {

        $wsFirstName = array_key_exists('first_name', $this->_data) && !empty($this->_data['first_name']) ? $this->_data['first_name'] : "Remarkety";
        $wsLastName = array_key_exists('last_name', $this->_data) && !empty($this->_data['last_name']) ? $this->_data['last_name'] : "API";

        if (!$this->_getWebServiceUser()) {
            $email = $this->_data['email'];

            $user = [
                'name' => self::WEB_SERVICE_ROLE,
                'email' => $email,
                'status'=> '1',
                'all_resources'=> '0',
                'resource' => [
                    'Remarkety_Mgconnector::admin',
                    'Remarkety_Mgconnector::admin_queue',
                    'Remarkety_Mgconnector::admin_version',
                    'Remarkety_Mgconnector::admin_config',
                    'Magento_Catalog::catalog',
                    'Magento_Customer::customer',
                    'Magento_Sales::actions_view',
                    'Magento_Cart::cart',
                    'Magento_Backend::store',
                    'Magento_SalesRule::quote',
                    'Magento_Newsletter::subscriber'
                ]
            ];

            $this->_integrationService->create($user);
            $consumer = $this->integration->load('remarkety', 'name');
            $this->oauthService->createAccessToken($consumer->getConsumerId());
            $token = $this->oauthService->getAccessToken($consumer->getConsumerId());
            $this->_data['key'] = $token->getToken();

        } else {
            $email = $this->_data['email'];
            $consumer = $this->integration->load('remarkety', 'name');

            $user = [
                'integration_id'=>$consumer->getId(),
                'name' => self::WEB_SERVICE_ROLE,
                'email' => $email,
                'status'=> '1',
                'all_resources'=>'1'
            ];
            $this->_integrationService->update($user);
            $this->oauthService->createAccessToken($consumer->getConsumerId());
            $token = $this->oauthService->getAccessToken($consumer->getConsumerId());
            $this->_data['key'] = $token->getToken();
        }

        $this->_resourceConfig->saveConfig('remarkety/mgconnector/api_key', $this->_data['key'], 'default', 0);

        return $this;
    }


    protected function _sendRequest($payload)
    {
        $this->mgconnectorRequest->makeRequest($payload);

        return $this;
    }


    public function installByCreateExtension()
    {
        $this->_webServiceConfiguration();
        // Make sure that store_id entry is an array
        if (!empty($this->_data['store_id']) && !is_array($this->_data['store_id'])) {
            $this->_data['store_id'] = (array)$this->_data['store_id'];
        }
//        // Create request for each store view separately
        foreach ($this->_data['store_id'] as $_storeId) {
            $store = $this->store->load($_storeId);
            $this->_sendRequest([
                'key' => $this->_data['key'],
                'email' => $this->_data['email'],
                'password' => $this->_data['password'],
                'acceptTerms' => $this->_data['terms'],
                'selectedView' => json_encode([
                    'website_id' => $store->getWebsiteId(),
                    'store_id' => $store->getGroupId(),
                    'view_id' => $_storeId,
                ]),
                'isNewUser' => true,
                'firstName' => $this->_data['first_name'],
                'lastName' => $this->_data['last_name'],
                'phone' => $this->_data['phone'],
                'httpUser' => $this->_data['http_user'],
                'httpPassword' => $this->_data['http_password'],
                'storeFrontUrl' => $store->getBaseUrl(UrlInterface::URL_TYPE_LINK),
                'viewName' => $store->getName()
            ]);
            $this->_markInstalled($_storeId);
        }
        return $this;
    }


    public function installByLoginExtension()
    {
            $this->_webServiceConfiguration();

        if (!empty($this->_data['store_id']) && !is_array($this->_data['store_id'])) {
            $this->_data['store_id'] = (array)$this->_data['store_id'];
        }

        foreach ($this->_data['store_id'] as $_storeId) {
            $store = $this->store->load($_storeId);

            $this->_sendRequest([
                'key' => $this->_data['key'],
                'email' => $this->_data['email'],
                'password' => $this->_data['password'],
                'acceptTerms' => $this->_data['terms'],
                'selectedView' => json_encode([
                    'website_id' => $store->getWebsiteId(),
                    'store_id' => $store->getGroupId(),
                    'view_id' => $_storeId,
                ]),
                'isNewUser' => false,
                'httpUser' => $this->_data['http_user'],
                'httpPassword' => $this->_data['http_password'],
                'storeFrontUrl' => $store->getBaseUrl(UrlInterface::URL_TYPE_LINK),
                'viewName' => $store->getName()
            ]);

            $this->_markInstalled($_storeId);
        }
        return $this;
    }


    public function upgradeExtension()
    {
        $webServiceUser = $this->_getWebServiceUser();

        $token = $this->oauthService->getAccessToken($webServiceUser['consumer_id']);
        $this->_data['email'] = $webServiceUser['email'];
        $this->_data['key'] = $token->getToken();


        $api_user = $this->_getWebServiceUser();

        $this->integration->load($api_user['integration_id'], 'integration_id')
            ->setData('email', $this->_data['email'])
            ->save();

        $this->_resourceConfig->saveConfig('remarkety/mgconnector/api_key', $this->_data['key'], 'default', 0);

        $this->_sendRequest([]);

        return $this;
    }


    public function completeExtensionInstallation()
    {
            $ver = $this->moduleResource->getDataVersion('Remarkety_Mgconnector');
            $this->_resourceConfig->saveConfig(self::XPATH_INSTALLED, $ver, 'default', 0);

            $intervals = $collection = $this->scopeConfigInterface->getValue('mgconnector_options/mgconnector_options_group/intervals');
        if (!empty($intervals)) {
            $this->_resourceConfig->saveConfig('remarkety/mgconnector/intervals', $intervals, 'default', 0);
        } else {
            $this->_resourceConfig->saveConfig('remarkety/mgconnector/intervals', "1,3,10", 'default', 0);
        }

            // remove old config entries if exist
            $this->_resourceConfig
                ->deleteConfig('mgconnector_options/mgconnector_options_group/api_key', 'default', 0)
                ->deleteConfig('mgconnector_options/mgconnector_options_group/intervals', 'default', 0);



        return $this;
    }


    protected function _getWebServiceUser()
    {

        $integration = $this->integration->load(self::WEB_SERVICE_USERNAME, 'name');
        return $integration->getData();
    }

    protected function _markInstalled($storeId)
    {
        $ver = $this->moduleResource->getDataVersion('Remarkety_Mgconnector');
        $this->_resourceConfig->saveConfig(
            self::XPATH_INSTALLED,
            $ver,
            self::STORE_SCOPE,
            $storeId
        );

        $response = $this->_session->getRemarketyLastResponseMessage();
        $response = !empty($response) ? $this->serialize->unserialize($response) : [];
        if (!empty($response['storePublicId'])) {
            $this->_webtracking->setRemarketyPublicId($storeId, $response['storePublicId']);
        }
        //check if we should use full categories path
        $use_full_cat_path = $this->scopeConfigInterface->getValue(ConfigHelper::USE_CATEGORIES_FULL_PATH);
        if (is_null($use_full_cat_path)) {
            $this->_resourceConfig->saveConfig(
                ConfigHelper::USE_CATEGORIES_FULL_PATH,
                1,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                0
            );
        }
        $this->_cache->cleanType('config');
        return $this;
    }


    public function getConfiguredStores()
    {
        $collection = $this->scopeConfigInterface->isSetFlag(self::XPATH_INSTALLED, self::STORE_SCOPE);

        return $collection;
    }
}
