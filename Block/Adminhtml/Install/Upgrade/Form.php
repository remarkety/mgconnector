<?php

namespace Remarkety\Mgconnector\Block\Adminhtml\Install\Upgrade;

use \Magento\Backend\Block\Widget\Form\Generic;
use \Remarkety\Mgconnector\Model\Install as InstallModel;

class Form extends Generic
{

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'    => 'edit_form',
                    'action' => $this->getUrl('/*/complete'),
                    'method' => 'post'
                ]
            ]
        );
        $form->setUseContainer(true);

        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => __('Upgrade Remarkety extension')
            )
        );

        $fieldset->addField('mode', 'hidden', array(
            'name' => 'data[mode]',
            'value' => 'upgrade',
        ));

            $fieldset->addField('instruction', 'note', array(
            'text'     => '',
            'label' => false,
            'after_element_html' =>
                '<p style="font-weight:bold;">' . __('Thank you for installing the Remarkety Magento plugin.
                You are one click away from finishing setting up Remarkety on your store and sending effective, targeted emails!')
                . '<br><br>'
                . __('It seems that you have already installed Remarkety on this website before. This
                version of the plugin will create a new API key, and automatically inform
                Remarkety. If this is a mistake, please <a href="%s">click here</a>.</p>', $this->getUrl('*/install/install', array('mode' => 'install_create')))
        ));

        $fieldset->addField('button', 'note', array(
            'label' => false,
            'name' => 'button',
            'after_element_html' => '<button id="submit-form" type="button" class="save"><span><span>'
                . 'Complete Installation' . '</span></span></button>',
        ));
        $this->setForm($form);
        return parent::_prepareForm();
    }
}