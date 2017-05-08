<?php

namespace Remarkety\Mgconnector\Controller\Adminhtml\Queue;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Queue extends \Magento\Backend\App\Action
{
    /**
     * Init action
     *
     * @return Remarkety_Mgconnector_Adminhtml_QueueController
     */
	protected $resultPageFactory;

	public function __construct(Context $context, PageFactory $resultPageFactory){

		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}

	public function execute()
	{
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Remarkety_Mgconnector::queue');
		$resultPage->addBreadcrumb(__('CMS'), __('CMS'));
		$resultPage->addBreadcrumb(__('Manage Remarkety Mgconnector Queue'), __('Manage Remarkety Mgconnector Queue'));
		$resultPage->getConfig()->getTitle()->prepend(__('Manage Remarkety Grid'));

		return $resultPage;
	}
}
