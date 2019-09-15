<?php
namespace Remarkety\Mgconnector\Controller\Adminhtml\Settings;

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
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
    protected $messageManager;
    protected $resultRedirect;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param ManagerInterface $messageManager
     * @param ConfigHelper $configHelper
     * @param ResultFactory $result
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        PageFactory $resultPageFactory,
        ManagerInterface $messageManager,
        ConfigHelper $configHelper,
        ResultFactory $result
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->configHelper = $configHelper;
        $this->messageManager = $messageManager;
        $this->resultRedirect = $result;
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
        $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
        $url = $this->_url->getUrl('mgconnector/settings/index');
        $resultRedirect->setUrl($url);

        return $resultRedirect;
    }
    private function saveSettings($data){
        if(isset($data['pos_id'])){
            $this->configHelper->setPOSAttributeCode($data['pos_id']);
        }
        return true;
    }
}