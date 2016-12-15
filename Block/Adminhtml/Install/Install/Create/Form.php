<?php

namespace Remarkety\Mgconnector\Block\Adminhtml\Install\Install\Create;

use \Magento\Backend\Block\Widget\Form\Generic;
use \Remarkety\Mgconnector\Model\Install as InstallModel;
use \Magento\Store\Model\System\Store;

class Form extends Generic
{

    protected $_store;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Registry $registry,
                                \Magento\Framework\Data\FormFactory $formFactory,
                                Store $store){
        $this->_store = $store;
        parent::__construct($context, $registry, $formFactory);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'    => 'edit_form',
                    'action' => $this->getUrl('*/*/complete'),
                    'method' => 'post'
                ]
            ]
        );
        $form->setUseContainer(true);


        $accountWithLoginUrl = $this->getUrl('*/install/install', array('mode' => InstallModel::MODE_INSTALL_LOGIN));
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Remarkety')]
            );
        $fieldset->addField('mode', 'hidden', array(
            'name' => 'data[mode]',
            'value' => 'install_create',
        ));

        $headingHtml =
        	'<p><b>' . 'Thank you for installing the Remarkety Magento plugin.
                You are one click away from finishing setting up Remarkety on your store and sending effective, targeted emails!'
                . '</b><br><br>'
                . 'The plugin will automatically create a Magento WebService API user so that
                Remarkety can synchronize with your store.'. '</p><hr/>'
                . '<h2>'.'Create a new Remarkety account' . '</h2>'
                . '<p>'.
                    sprintf(
                    'Already registered to Remarkety? <a href="%s">Click here</a>'
                		, $accountWithLoginUrl)
                . '</p>';

        $fieldset->addField('instruction', 'note', array(
            'text' => '',
            'label' => false,
            'after_element_html' =>$headingHtml,

        ));
        $fieldset->addField('email', 'text', array(
            'label' => 'Email address for Remarkety account:',
            'name' => 'data[email]',
            'required' => true,
            'class' => 'validate-email required-entry _required',
            'style' => 'float:left',
        ));

        $fieldset->addField('first_name', 'text', array(
            'label' => 'First Name:',
            'name' => 'data[first_name]',
            'required' => true,
            'class' => 'required-entry'
        ));

        $fieldset->addField('last_name', 'text', array(
            'label' => 'Last Name:',
            'name' => 'data[last_name]',
            'required' => true,
            'class' => 'required-entry'
        ));

        $fieldset->addField('phone', 'text', array(
            'label' => 'Phone:',
            'name' => 'data[phone]',
            'required' => true,
            'class' => 'required-entry'
        ));

        $fieldset->addField('password', 'password', array(
            'label' => 'Password:',
            'name' => 'data[password]',
            'required' => true,
            'class' => 'required-entry admin__control-text'
        ));

        $fieldset->addField('store_id', 'multiselect', array(
            'name' => 'data[store_id]',
            'label' => 'Sync Remarkety with this view:',
            'required' => true,
            'values' => $this->_store->getStoreValuesForForm(false, false),
        ));

        $fieldset->addField('terms', 'checkbox', array(
            'label' => false,
            'name' => 'data[terms]',
            'checked' => false,
            'value' => '1',
            'class' => 'required-entry',
            'after_element_html' => 'I agree to Remarkety’s <a href="%s">terms of use</a>.', '#',
        ));

        $fieldset->addField('button', 'note', array(
            'label' => false,
            'name' => 'button',
            'after_element_html' => '<button id="submit-form" type="button" class="save"><span><span>'
                . 'Create New Account And Connect' . '</span></span></button>',
        ));
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
