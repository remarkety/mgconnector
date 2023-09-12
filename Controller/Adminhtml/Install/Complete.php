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
use \Remarkety\Mgconnector\Model\Install as InstallModel;

class Complete extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $installModel;
    protected $_messageManager;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        InstallModel $installModel
    ) {
        parent::__construct($context);
        $this->installModel = $installModel;
        $this->resultPageFactory = $resultPageFactory;
        $this->_messageManager = $context->getMessageManager();
    }

    public function execute()
    {
        $redirectBack = true;
        $params = $this->getRequest()->getParams();

        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();

            try {
                $install = $this->installModel
                    ->setData($params['data']);

                switch ($params['data']['mode']) {
                    case InstallModel::MODE_INSTALL_CREATE:
                        $install->installByCreateExtension();
                        break;
                    case InstallModel::MODE_INSTALL_LOGIN:
                        $install->installByLoginExtension();
                        break;
                    case InstallModel::MODE_UPGRADE:
                        $install->upgradeExtension();
                        break;
                    case InstallModel::MODE_COMPLETE:
                        $install->completeExtensionInstallation();
                        break;
                    default:
                        throw new \Exception('Selected mode can not be handled.');
                }
//
                $redirectBack = false;
            } catch (\Exception $e) {
                $this->_messageManager->addError(__($e->getMessage()));

                $this->_redirect('*/install/install');
            }
        }

        if ($redirectBack) {
            $mode = isset($params['data']['mode']) ? $params['data']['mode'] : null;
            $this->_redirect('*/install/install', ['mode' => $mode]);
        } else {
            $this->_redirect('*/install/install', ['mode' => 'welcome']);
        }
    }
}
