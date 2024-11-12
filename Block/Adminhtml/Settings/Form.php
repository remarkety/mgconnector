<?php
namespace Remarkety\Mgconnector\Block\Adminhtml\Settings;

use Magento\Framework\View\Element\Template;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Helper\RewardPointsFactory;

class Form extends Template
{
    private $formKey;
    private $attributesCollection;
    private $configHelper;

    private $current_pos_id;
    private $is_fpt_enabled;
    private $is_aw_points_enabled;
    private $is_aw_points_plugin_exists = false;
    private $event_cart_view;
    private $event_search_view;
    private $event_category_view;
    private $is_cart_auto_coupon_enabled;

    public function __construct(
        Template\Context $context,
        array $data,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Customer\Model\ResourceModel\Attribute\Collection $attributesCollection,
        ConfigHelper $configHelper,
        RewardPointsFactory $rewardPointsFactory
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
        $this->attributesCollection = $attributesCollection;
        $this->configHelper = $configHelper;
        $this->current_pos_id = $configHelper->getPOSAttributeCode();
        $this->event_cart_view = $configHelper->isEventCartViewEnabled();
        $this->event_search_view = $configHelper->isEventSearchViewEnabled();
        $this->event_category_view = $configHelper->isEventCategoryViewEnabled();
        $this->is_fpt_enabled = $configHelper->getWithFixedProductTax();
        $this->is_cart_auto_coupon_enabled = $configHelper->isCartAutoCouponEnabled();
        $this->is_aw_points_enabled = $configHelper->isAheadworksRewardPointsEnabled();
        $this->email_consent_enabled = $configHelper->getValue(ConfigHelper::EMAIL_CONSENT_ENABLED);
        $this->email_consent_checkbox_position = $configHelper->getValue(ConfigHelper::EMAIL_CONSENT_CHECKBOX_POSITION);
        $this->email_consent_checkbox_lable_value = $configHelper->getValue(ConfigHelper::EMAIL_CONSENT_CHECKBOX_LABEL_VALUE);
        $this->sms_consent_enabled = $configHelper->getValue(ConfigHelper::SMS_CONSENT_ENABLED);
        $this->sms_consent_checkbox_position = $configHelper->getValue(ConfigHelper::SMS_CONSENT_CHECKBOX_POSITION);
        $this->sms_consent_checkbox_lable_value = $configHelper->getValue(ConfigHelper::SMS_CONSENT_CHECKBOX_LABEL_VALUE);
        $this->popup_enabled = $configHelper->getValue(ConfigHelper::POPUP_ENABLED);
        $aw_service = $rewardPointsFactory->create();
        if ($aw_service) {
            $this->is_aw_points_plugin_exists = true;
        }
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getCustomerAddress()
    {
        return $this->configHelper->getCustomerAddressType();
    }

    public function getPosIdOptions()
    {
        $attribute_data = [];
        foreach ($this->attributesCollection as $item) {
            $name = $item->getFrontendLabel();
            if (empty($name)) {
                continue;
            }
            $attribute_data[$item->getAttributeCode()] = $name;
        }
        return $attribute_data;
    }

    public function getCurrentPOSCode()
    {
        return $this->current_pos_id;
    }

    public function getEnabledDisabledOptions()
    {
        $attribute_data = [];

        $attribute_data[0] = 'Disable';
        $attribute_data[1] = 'Enable';

        return $attribute_data;
    }

    /**
     * @return int|mixed
     */
    public function getEventCartView()
    {
        return $this->event_cart_view;
    }

    /**
     * @return int|mixed
     */
    public function getEventSearchView()
    {
        return $this->event_search_view;
    }

    /**
     * @return int|mixed
     */
    public function getEventCategoryView()
    {
        return $this->event_category_view;
    }

    public function getFptEnabled()
    {
        return $this->is_fpt_enabled;
    }

    public function isAWRewradPointsEnabled()
    {
        return $this->is_aw_points_enabled;
    }

    /**
     * @return bool
     */
    public function isIsAwPointsPluginExists()
    {
        return $this->is_aw_points_plugin_exists;
    }

    /**
     * @return bool
     */
    public function getCartAutoCouponEnabled()
    {
        return $this->is_cart_auto_coupon_enabled;
    }

    /**
     * @return int|mixed
     */
    public function getEmailConsentEnabled()
    {
        return $this->email_consent_enabled;
    }

    /**
     * @return int|mixed
     */
    public function getEmailConsentCheckboxPosition()
    {
        return $this->email_consent_checkbox_position;
    }

    /**
     * @return int|mixed
     */
    public function getEmailConsentCheckboxLabelValue()
    {
        return $this->email_consent_checkbox_lable_value;
    }

    /**
     * @return int|mixed
     */
    public function getSMSConsentEnabled()
    {
        return $this->sms_consent_enabled;
    }

    /**
     * @return int|mixed
     */
    public function getSMSConsentCheckboxPosition()
    {
        return $this->sms_consent_checkbox_position;
    }

    /**
     * @return mixed
     */
    public function getSMSConsentCheckboxLabelValue()
    {
        return $this->sms_consent_checkbox_lable_value;
    }

    /**
     * @return array
     */
    public function getFormFieldPositions()
    {
        return range(0 , 1000, 10);
    }

    /**
     * @return int|mixed
     */
    public function getPopupEnabled()
    {
        return $this->popup_enabled;
    }
}
