<?php

/**
 * Adminhtml configure block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Queue;

use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Helper\Data;

//use \Magento\Framework\Module\Manager;
//use \Remarkety\Mgconnector\Model\QueueFactory;
//use \Magento\Catalog\Model\ProductFactory;

class Configure extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Prepare block
     */

    public function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'Remarkety_Mgconnector';
        $this->_controller = 'adminhtml_queue';
        $this->_headerText = $this->__('Queue Configuration');
    }
}
