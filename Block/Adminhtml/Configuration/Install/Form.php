<?php

/**
 * Adminhtml configuration install form block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Configuration\Install; class Form extends \Magento\Backend\Block\Widget
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
                'legend' => $this->__('Install Remarkety extension')
            )
        );

        $fieldset->addField('mode', 'hidden', array(
            'name' => 'data[mode]',
            'value' => 'install',
        ));

        $instruction = $fieldset->addField('instruction', 'note', array(
            'text' => '',
            'label' => false,
            'after_element_html' =>
                '<p style="font-weight:bold;">' . $this->__('Thank you for installing the Remarkety Magento plugin.
                You are one click away from finishing setting up Remarkety on your store and sending effective, targeted emails!')
                . '<br><br>'
                . $this->__('The plugin will automatically create a Magento WebService API user so that
                Remarkety can synchronize with your store.') . '</p>',
        ));
        $instruction->getRenderer()->setTemplate('mgconnector/widget/form/renderer/fieldset/element.phtml');

        $fieldset->addField('email', 'text', array(
            'label' => $this->__('Email address for the Remarkety account:'),
            'name' => 'data[email]',
            'required' => true,
            'class' => 'validate-email',
            'after_element_html' => '<small style="float:left;width:100%;">' . $this->__(
                    'If you’ve already registered to Remarkety, please use the email you used to open your account.
                    If you haven’t, an email will be sent to this address with the login information. You will then be able
                    to choose your password.'
                ) . '</small>',
            'style' => 'float:left',
        ));

        $fieldset->addField('store_id', 'select', array(
            'name' => 'data[store_id]',
            'label' => $this->__('Sync Remarkety with this view:'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
        ));

        $fieldset->addField('terms', 'checkbox', array(
            'label' => false,
            'name' => 'data[terms]',
            'checked' => false,
            'value' => '1',
            'class' => 'required-entry',
            'after_element_html' => $this->__('I agree to Remarkety’s <a href="%s">terms of use</a>.', '#'),
        ));

        $fieldset->addField('button', 'note', array(
            'label' => false,
            'name' => 'button',
            'after_element_html' => '<button type="button" class="save" onclick="editForm.submit();"><span><span>'
                . $this->__('Complete Installation') . '</span></span></button>'
        ));

        return parent::_prepareForm();
    }
}