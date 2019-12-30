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
        if($request->getMethod() === "POST"){
            if($this->saveSettings($request->getPost())){
                $this->messageManager->addSuccessMessage('Settings saved');
            } else {
                $this->messageManager->addErrorMessage('Could not save the settings');
            }
            return $this->returnRedirect();
        }
        return  $resultPage = $this->resultPageFactory->create();
    }

    private function returnRedirect(){
        /**
         * @var Redirect $resultRedirect
         */
        $resultRedirect = $this->resultRedirect->create();
        $url = $this->_url->getUrl('mgconnector/settings/index');
        $resultRedirect->setUrl($url);

        return $resultRedirect;
    }
    private function saveSettings($data){
        if(isset($data['pos_id'])){
            $this->configHelper->setPOSAttributeCode($data['pos_id']);
        }

        if(isset($data['category_view'])) {
            $this->configHelper->setEventCategoryViewEnabled($data['category_view'] == 1);
        }

        if(isset($data['search_view'])) {
            $this->configHelper->setEventSearchViewEnabled($data['search_view'] == 1);
        }

        if(isset($data['cart_updated'])) {
            $this->configHelper->setEventCartViewEnabled($data['cart_updated'] == 1);
        }

        if(isset($data['with_fpt'])) {
            $this->configHelper->setWithFixedProductTax($data['with_fpt'] == 1);
        }

        if(isset($data['aw_rewards_integrate'])) {
            $this->configHelper->setAheadworksRewardPointsEnabled($data['aw_rewards_integrate'] == 1);
        }

        return true;
    }
}
