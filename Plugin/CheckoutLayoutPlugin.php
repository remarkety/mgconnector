<?php
namespace Remarkety\Mgconnector\Plugin;

use Remarkety\Mgconnector\Helper\ConfigHelper;
use Magento\Customer\Model\Session;

class CheckoutLayoutPlugin
{

    /**
     * @var ConfigHelper
     */
    private $remarketyConfigHelper;

    /**
     * @var Session
     */
    private $_customerSession;

    public function __construct(
        ConfigHelper $remarketyConfigHelper,
        Session $customerSession
    ) {
        $this->remarketyConfigHelper = $remarketyConfigHelper;
        $this->_customerSession = $customerSession;
    }

    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $processor, $jsLayout){
        $fields = [];
        if($this->remarketyConfigHelper->getValue(ConfigHelper::EMAIL_CONSENT_ENABLED) == 1) {
            $fields[] = [
                'id' => 'rm_email_consent',
                'label' => $this->remarketyConfigHelper->getValue(ConfigHelper::EMAIL_CONSENT_CHECKBOX_LABEL_VALUE) ?? 'Email marketing consent',
                'sortOrder' => $this->remarketyConfigHelper->getValue(ConfigHelper::EMAIL_CONSENT_CHECKBOX_POSITION) ?? 900,
            ];
        }

        if($this->remarketyConfigHelper->getValue(ConfigHelper::SMS_CONSENT_ENABLED) == 1) {
            $fields[] = [
                'id' => 'rm_sms_consent',
                'label' => $this->remarketyConfigHelper->getValue(ConfigHelper::SMS_CONSENT_CHECKBOX_LABEL_VALUE) ?? 'SMS marketing consent',
                'sortOrder' => $this->remarketyConfigHelper->getValue(ConfigHelper::SMS_CONSENT_CHECKBOX_POSITION) ?? 900,
            ];
        }

        foreach ($fields as $key => $field) {
            $newField = [
                'component' => 'Magento_Ui/js/form/element/abstract',
//                'component' => 'Remarkety_Mgconnector/js/form/element/checkbox',
                'config' => [
                    'id' => $field['id'],
//                    'customScope' => 'shippingAddress.custom_attributes',
                    'customScope' => 'shippingAddress',
//                    'customEntry' => null,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/checkbox',
                    'description' => $field['label'],
                ],
//                'dataScope' => 'shippingAddress.custom_attributes.' . $field['id'],
//                'dataScope' => 'shippingAddress.' . $field['id'],
                'dataScope' => 'shippingAddress.extension_attributes.' . $field['id'],
//                'dataScope' => 'customCheckoutForm.' . $field['id'],
//                'label' => $field['label'],
                'description' => $field['label'],
                'provider' => 'checkoutProvider',
                'validation' => [
                    'required-entry' => false
                ],
                'options' => [],
                'filterBy' => null,
                'customEntry' => null,
                'visible' => true,
                'id' => $field['id'],
                'sortOrder' => $field['sortOrder'],
                'value' => false,
            ];

            if ($this->_customerSession->isLoggedIn()) {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['before-form']['children'][$field['id']] = $newField;
            } else {
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][$field['id']] = $newField;
            }
        }


        return $jsLayout;
    }
}
