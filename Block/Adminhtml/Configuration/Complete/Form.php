<?php

/**
 * Adminhtml configuration complete form block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Configuration\Complete; class Form extends \Magento\Backend\Block\Widget\Form
{
    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/complete'),
            'method' => 'post',
        ));
        $form->setFieldContainerIdPrefix('data');
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => $this->__('Installation Complete')
            )
        );

        $fieldset->addField('mode', 'hidden', array(
            'name' => 'data[mode]',
            'value' => 'complete',
        ));

        $instruction = $fieldset->addField('instruction', 'note', array(
            'text' => '',
            'label' => false,
            'after_element_html' => '<p style="font-weight:bold;">' . $this->__('Installation complete!') . '</p>'
        ));
        $instruction->getRenderer()->setTemplate('mgconnector/widget/form/renderer/fieldset/element.phtml');

        $response = unserialize(Mage::getStoreConfig('remarkety/mgconnector/last_response'),true);
        $fieldset->addField('response', 'note', array(
            'label' => false,
            'after_element_html' => !empty($response['info']) ? $response['info'] : $this->__('There is no response to display')
        ));

        $fieldset->addField('button', 'note', array(
            'label' => false,
            'name' => 'button',
            'after_element_html' => '<button type="button" class="save" onclick="editForm.submit();"><span><span>'
                . $this->__('Done') . '</span></span></button>'
        ));

        return parent::_prepareForm();
    }
}