<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Install\Grid\Render;
use Remarkety\Mgconnector\Helper\ConfigHelper;

/**
 * Store render website
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var $configHelper ConfigHelper
         */
        $configHelper = $objectManager->get('Remarkety\Mgconnector\Helper\ConfigHelper');
        $installed = $configHelper->isStoreInstalled($row->getStoreId());
        if($installed){
            $ret = '<span style="color:green">'. __('Connected') .'</span>';

            $publicId = $configHelper->getRemarketyPublicId($row->getStoreId());
            if(empty($publicId)){
                $ret .= '<br />';
                $ret .= '<span style="color:red">(' . __('Missing Remarkety\'s store id; website tracking and live updates will not work.') . ')</span>';
            }

            return $ret;
        } else {
            return '<button id="install_button_'.$row->getStoreId().'" type="button" class="scalable save" onclick="window.location = \''. $this->getUrl('*/install/install', ['mode' => \Remarkety\Mgconnector\Model\Install::MODE_INSTALL_LOGIN, 'store' => $row->getStoreId()]) .'\'" style="">
                Connect to Remarkety            
            </button>';
        }
    }
}
