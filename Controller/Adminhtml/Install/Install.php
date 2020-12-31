<?php

/**
 * Adminhtml Install controller
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Controller\Adminhtml\Install;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Install extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
        //                                \Magento\Framework\App\RequestInterface $request
    ) {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
//        $this->requestInterface = $request;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Remarkety_Mgconnector::installation');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Remarkety Installation'));
        return $resultPage;
    }
}
