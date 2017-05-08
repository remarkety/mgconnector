<?php

namespace Remarkety\Mgconnector\Block\Adminhtml\Install\Welcome\Form;

use \Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{

    public function __construct(\Magento\Backend\Block\Template\Context $context,
                                \Magento\Framework\Registry $registry,
                                \Magento\Framework\Data\FormFactory $formFactory
                                ){
        parent::__construct($context, $registry, $formFactory);
    }

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
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'general',
            array(
                'legend' => __('Remarkety')
            )
        );

        $fieldset->addField('mode', 'hidden', array(
            'name' => 'data[mode]',
            'value' => 'complete',
        ));
        $fieldset->addField('instruction', 'note', array(
            'text' => '<p style="font-weight:bold;font-size:25px;">' . __('Welcome to Remarkety - What\'s next?') . '</p>
            <ol style="list-style-type:decimal;margin-left:20px;font-weight:bold;font-size:12px;">
                <li>Sign in to your account <a href="https://app.remarkety.com/?utm_source=plugin&utm_medium=link&utm_campaign=magento-plugin" target="_blank">here</a></li>
                <li>Create campaigns, send emails and monitor results.</li>
                <li>Increase sales and customer\'s Life Time Value</li>
                <li>Need help? We are here for you: <a href="mailto:support@remarkety.com">support@remarkety.com</a> <a href="tel:%28%2B1%20800%20570-7564">(+1 800 570-7564)</a></li>
            </ol>
            ',
            'label' => false,
        ));

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
