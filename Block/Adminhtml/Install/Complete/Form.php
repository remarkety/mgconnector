<?php

namespace Remarkety\Mgconnector\Block\Adminhtml\Install\Complete;

use \Magento\Backend\Block\Widget\Form\Generic;
use \Remarkety\Mgconnector\Model\Install as InstallModel;

class Form extends Generic
{

    private $serialize;
    private $session;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize
    ) {
        $this->session = $session;
        $this->serialize = $serialize;
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

        $fieldset = $form->addFieldset(
            'general',
            [
                'legend' => __('Installation Complete')
            ]
        );

        $fieldset->addField('mode', 'hidden', [
            'name' => 'data[mode]',
            'value' => 'complete',
        ]);

            $fieldset->addField('instruction', 'note', [
            'text' => '',
            'label' => false,
            'after_element_html' => '<p style="font-weight:bold;">' . __('Installation complete!') . '</p>'
            ]);
        $response = $this->session->getRemarketyLastResponseStatus();
        $response = !empty($response) ? $this->serialize->unserialize($response) : [];
        $fieldset->addField('response', 'note', [
            'label' => false,
            'after_element_html' => !empty($response['info']) ? $response['info'] : __('There is no response to display')
        ]);

        $fieldset->addField('button', 'note', [
        'label' => false,
        'name' => 'button',
        'after_element_html' => '<button id="submit-form" type="button" class="save"><span><span>'
            . 'Done' . '</span></span></button>',
        ]);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
