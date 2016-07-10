<?php


namespace Remarkety\Mgconnector\Helper;
use Magento\TestFramework\Inspection\Exception;
use \Remarkety\Mgconnector\Model\Install as InstallModel;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(\Magento\Framework\App\Helper\Context $context,
                                \Magento\Framework\Module\ModuleResource $moduleResource,
                                \Magento\Integration\Model\Integration $integration,
                                \Magento\Customer\Model\Session $session,
                                InstallModel $installModel

    ){

        $this->integration = $integration;
        $this->moduleResource = $moduleResource;
        $this->session = $session;
        $this->installModel = $installModel;
        parent::__construct($context);
    }

	public function getInstalledVersion()
    {
        return $this->moduleResource->getDataVersion('Remarkety_Mgconnector');
    }


    public function getMode()
    {
        $mode = InstallModel::MODE_INSTALL_CREATE;

        $webServiceUser = $this->integration->load(InstallModel::WEB_SERVICE_USERNAME, 'name');
        if($webServiceUser->getData()) {
            $mode = InstallModel::MODE_UPGRADE;
        }

        $response = $this->session->getRemarketyLastResponseStatus();
        if($response == 1) {
            $mode = InstallModel::MODE_COMPLETE;
        } elseif($response == 0) {
            $mode = InstallModel::MODE_INSTALL_CREATE;
        }
        $configuredStores = $this->installModel->getConfiguredStores();
        if($configuredStores) {
            $mode = InstallModel::MODE_WELCOME;
        }

        $forceMode = $this->_request->getParam('mode', false);
        if(!empty($forceMode)) {
            $mode = $forceMode;
        }

        if(!in_array($mode, array(
            InstallModel::MODE_INSTALL_CREATE,
            InstallModel::MODE_INSTALL_LOGIN,
            InstallModel::MODE_UPGRADE,
            InstallModel::MODE_COMPLETE,
            InstallModel::MODE_WELCOME,
        ))) {
            throw new \Exception('Installation mode can not be handled.');
        }
        return $mode;
    }
}
