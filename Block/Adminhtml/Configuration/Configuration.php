<?php

/**
 * Adminhtml configuration configuration block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Configuration; class Configuration extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Prepare block
     */
    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'configuration_id';
        $this->_blockGroup = 'mgconnector';
        $this->_controller = 'adminhtml_configuration';
        $this->_mode = 'configuration';

        $ver = Mage::getConfig()->getModuleConfig("Remarkety_Mgconnector")->version;
        $this->_headerText = $this->__('Remarkety configuration (version: %s)', $ver);

        $this->_removeButton('back');
        $this->_removeButton('reset');
        $this->_addButton('save', array(
            'label'     => Mage::helper('adminhtml')->__('Save'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save',
        ), 1);

        $this->_addButton('reinstall', array(
            'label'     => Mage::helper('adminhtml')->__('Reinstall'),
            'onclick'   => "return confirm('Are you sure?') ? window.location = '" . $this->getUrl('*/*/reinstall')."' : false;",
            'class'     => 'delete',
        ), 0);
    }
}