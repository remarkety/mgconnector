<?php

/**
 * Adminhtml configure form block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Queue\Configure;

class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */


    protected function _prepareForm()
    {
        echo 'test';
        exit;
//        $form = new Varien_Data_Form(array(
//            'id' => 'edit_form',
//            'action' => $this->getUrl('*/queue/save'),
//            'method' => 'post',
//        ));
//        $form->setFieldContainerIdPrefix('data');
//        $form->setUseContainer(true);
//        $this->setForm($form);
//
//        $fieldset = $form->addFieldset(
//            'general',
//            array(
//                'legend' => $this->__('Remarkety Configuration')
//            )
//        );
//
//        $fieldset->addField('mode', 'hidden', array(
//            'name' => 'data[mode]',
//            'value' => 'configuration',
//        ));
//
//        $fieldset->addField('intervals', 'text', array(
//            'label' => $this->__('Intervals:'),
//            'name' => 'data[intervals]',
//            'required' => true,
//            'after_element_html' => '<small style="float:left;width:100%;">' . $this->__(
//                    'This defines the queue retry interval.<br/>
//            		Type the amount of minutes between retries, separated by commas. For example "1,3,10" -
//                    the second attempt will be after 1 minute, third after 3 minutes,<br/>
//                    and fourth after 10 minutes. If the last attempt is unsuccessful,
//                    the status will be changed to "failed" and will not be retried anymore.'
//                ) . '</small>',
//            'value' => Mage::getStoreConfig('remarkety/mgconnector/intervals'),
//            'style' => 'float:left',
//        ));
//
//        $button = $fieldset->addField('button', 'note', array(
//            'label' => false,
//            'name' => 'button',
//            'after_element_html' => '<button type="button" class="save" onclick="editForm.submit();"><span><span>'
//                . $this->__('Save') . '</span></span></button>',
//        ));
//        $button->getRenderer()->setTemplate('mgconnector/element.phtml');
//
//        return parent::_prepareForm();
    }
}