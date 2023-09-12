<?php

/**
 * Adminhtml Install controller
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Controller\Adminhtml\Install;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use \Remarkety\Mgconnector\Model\Install as InstallModel;

class Reinstall extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $installModel;
    protected $_messageManager;
    protected $config;
    protected $storeManager;
    protected $session;
    private $configHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        InstallModel $installModel,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        ConfigHelper $configHelper
    ) {
        parent::__construct($context);
        $this->installModel = $installModel;
        $this->resultPageFactory = $resultPageFactory;
        $this->_messageManager = $context->getMessageManager();
        $this->config = $resourceConfig;
        $this->storeManager = $storeManager;
        $this->session = $customerSession;
        $this->configHelper = $configHelper;
    }

    public function execute()
    {
        $config = $this->config;
        $config
            ->deleteConfig(\Remarkety\Mgconnector\Model\Install::XPATH_INSTALLED, 'default', 0)
            ->deleteConfig('remarkety/mgconnector/api_key', 'default', 0)
            ->deleteConfig('remarkety/mgconnector/intervals', 'default', 0)
            ->deleteConfig('remarkety/mgconnector/last_response_status', 'default', 0)
            ->deleteConfig('remarkety/mgconnector/last_response_message', 'default', 0);
        $this->configHelper->setCategoriesFullPath($this->configHelper->useCategoriesFullPath());

        foreach ($this->storeManager->getWebsites() as $_website) {
            foreach ($_website->getGroups() as $_group) {
                foreach ($_group->getStores() as $_store) {
                    $scope = $_store->getStoreId();
                    $config->deleteConfig(\Remarkety\Mgconnector\Model\Install::XPATH_INSTALLED, 'stores', $scope);
                    $config->deleteConfig(\Remarkety\Mgconnector\Model\Webtracking::RM_STORE_ID, 'stores', $scope);
                }
            }
        }


        $this->session->unsRemarketyLastResponseMessage();
        $this->session->unsRemarketyLastResponseStatus();

        $this->_redirect('*/install/install', ['mode' => 'install_create']);
    }
}
