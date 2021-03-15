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
    private $event_add_to_cart_view;

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
        $this->event_add_to_cart_view = $configHelper->isEventAddToCartViewEnabled();
        $this->is_fpt_enabled = $configHelper->getWithFixedProductTax();
        $this->is_aw_points_enabled = $configHelper->isAheadworksRewardPointsEnabled();
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

    public function getEventAddToCartView()
    {
        return $this->event_add_to_cart_view;
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
}
