<?php

namespace Remarkety\Mgconnector\Block\Adminhtml\Install\Install\Login;

use \Magento\Backend\Block\Widget\Form\Generic;
use \Remarkety\Mgconnector\Model\Install as InstallModel;

class Form extends Generic
{
    protected $_systemStore;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore
    )
    {
        parent::__construct($context, $registry, $formFactory);
        $this->_systemStore = $systemStore;

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


        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Install Remarkety extension')]
        );
        $fieldset->addField('mode', 'hidden', array(
            'name' => 'data[mode]',
            'value' => 'install_login',
        ));
        $noAccountUrl = $this->getUrl('*/install/install', array('mode' => InstallModel::MODE_INSTALL_CREATE));

        $html = '<small style="float:left;width:100%;">' . sprintf(__(
                'Don\'t have a Remarkety account yet? <a href="%s">Click here</a>'
            ), $noAccountUrl) . '</small>';

        $fieldset->addField('email', 'text', array(
            'label' => __('Email address for the Remarkety account:'),
            'name' => 'data[email]',
            'required' => true,
            'class' => 'validate-email',
            'after_element_html' => $html,
            'style' => 'float:left',
        ));

        $fieldset->addField('password', 'password', array(
            'label' =>__('Password:'),
            'name' => 'data[password]',
            'required' => true,
            'class' => 'required-entry admin__control-text'
        ));

        $options = $this->_systemStore->getStoreValuesForForm(false, false);
        $selected_store_id = $this->getRequest()->getParam('store');

        $fieldset->addField('store_id', 'select', array(
            'name' => 'data[store_id]',
            'label' =>__('Sync Remarkety with this view:'),
            'values' => $options,
            'value' => $selected_store_id
        ));

        $fieldset->addField('http_note', 'note', [
            'label' => false,
            'after_element_html' => 'If your website is password-protected, please enter the credentials here:',
            'name' => 'http_note',
        ]);

        $fieldset->addField('http_user', 'text', array(
            'label' =>__('Website Basic Auth Username:'),
            'name' => 'data[http_user]',
            'required' => false,
            'class' => 'admin__control-text'
        ));

        $fieldset->addField('http_password', 'password', array(
            'label' =>__('Website Basic Auth Username:'),
            'name' => 'data[http_password]',
            'required' => false,
            'class' => 'admin__control-text'
        ));

      $fieldset->addField('button', 'note', array(
        'label' => false,
            'name' => 'button',
            'after_element_html' => '<button id="submit-form" type="button" class="save"><span><span>'
    . 'Login And Connect' . '</span></span></button>',
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function currentStore($id = null) {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $manager = $om->get('Magento\Store\Model\StoreManagerInterface');
        return $manager->getStore($id);
    }
}
