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
use Magento\Store\Model\StoreManager;
use Remarkety\Mgconnector\Helper\ConfigHelper;

class Webhooks extends \Magento\Backend\App\Action
{
    protected $configHelper;
    protected $storeManager;

    public function __construct(
        Context $context,
        ConfigHelper $configHelper,
        StoreManager $storeManager
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $enabled = $this->getRequest()->getParam('enabled');
        if ($enabled == 0) {
            //disable
            $this->configHelper->setWebhooksGloballStatus(false);
        } else {
            //enable
            $this->configHelper->setWebhooksGloballStatus(true);
            //get remarkety store id for eash enabled store

        }
        $this->_redirect('*/install/install');
    }
}
