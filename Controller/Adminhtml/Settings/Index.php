<?php
namespace Remarkety\Mgconnector\Controller\Adminhtml\Settings;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Remarkety\Mgconnector\Helper\ConfigHelper;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    protected $configHelper;
    protected $resultRedirect;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param PageFactory                         $resultPageFactory
     * @param ConfigHelper                        $configHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PageFactory $resultPageFactory,
        ConfigHelper $configHelper
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirect = $context->getResultRedirectFactory();
    }

    /**
     * Load the page defined in view/adminhtml/layout/mgconnector_settings_index.xml
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        if ($request->getMethod() === "POST") {
            if ($this->saveSettings($request->getPost())) {
                $this->messageManager->addSuccessMessage('Settings saved');
            } else {
                $this->messageManager->addErrorMessage('Could not save the settings');
            }
            return $this->returnRedirect();
        }
        return  $resultPage = $this->resultPageFactory->create();
    }

    private function returnRedirect()
    {
        /**
         * @var Redirect $resultRedirect
         */
        $resultRedirect = $this->resultRedirect->create();
        $url = $this->_url->getUrl('mgconnector/settings/index');
        $resultRedirect->setUrl($url);

        return $resultRedirect;
    }
    private function saveSettings($data)
    {
        if (isset($data['pos_id'])) {
            $this->configHelper->setPOSAttributeCode($data['pos_id']);
        }

        if (isset($data['category_view'])) {
            $this->configHelper->setEventCategoryViewEnabled($data['category_view'] == 1);
        }

        if (isset($data['search_view'])) {
            $this->configHelper->setEventSearchViewEnabled($data['search_view'] == 1);
        }

        if (isset($data['cart_updated'])) {
            $this->configHelper->setEventCartViewEnabled($data['cart_updated'] == 1);
        }

        if (isset($data['cart_auto_coupon'])) {
            $this->configHelper->setCartAutoCouponEnabled($data['cart_auto_coupon'] == 1);
        }

        if (isset($data['with_fpt'])) {
            $this->configHelper->setWithFixedProductTax($data['with_fpt'] == 1);
        }

        if (isset($data['aw_rewards_integrate'])) {
            $this->configHelper->setAheadworksRewardPointsEnabled($data['aw_rewards_integrate'] == 1);
        }

        if (isset($data['customer_address'])) {
            $this->configHelper->setCustomerAddressType($data['customer_address']);
        }

        if (isset($data['email_consent'])) {
            $this->configHelper->setValue(ConfigHelper::EMAIL_CONSENT_ENABLED, $data['email_consent'] == 1);
        }

        if (isset($data['email_consent_checkbox_position'])) {
            $this->configHelper->setValue(ConfigHelper::EMAIL_CONSENT_CHECKBOX_POSITION, $data['email_consent_checkbox_position']);
        }

        if (isset($data['email_consent_checkbox_lable_value'])) {
            $this->configHelper->setValue(ConfigHelper::EMAIL_CONSENT_CHECKBOX_LABEL_VALUE, $data['email_consent_checkbox_lable_value']);
        }

        if (isset($data['sms_consent'])) {
            $this->configHelper->setValue(ConfigHelper::SMS_CONSENT_ENABLED, $data['sms_consent'] == 1);
        }

        if (isset($data['sms_consent_checkbox_position'])) {
            $this->configHelper->setValue(ConfigHelper::SMS_CONSENT_CHECKBOX_POSITION, $data['sms_consent_checkbox_position']);
        }

        if (isset($data['sms_consent_checkbox_lable_value'])) {
            $this->configHelper->setValue(ConfigHelper::SMS_CONSENT_CHECKBOX_LABEL_VALUE, $data['sms_consent_checkbox_lable_value']);
        }

        if (isset($data['popup_enabled'])) {
            $this->configHelper->setValue(ConfigHelper::POPUP_ENABLED, $data['popup_enabled']);
        }

        if (isset($data['is_not_visible_product_enabled'])) {
            $this->configHelper->setValue(ConfigHelper::IS_NOT_VISIBLE_PRODUCT_ENABLED, $data['is_not_visible_product_enabled']);
        }

        if (isset($data['customer_group_for_price_rules'])) {
            $this->configHelper->setValue(ConfigHelper::CUSTOMER_GROUP_FOR_PRICE_RULES, $data['customer_group_for_price_rules']);
        }

        return true;
    }
}
