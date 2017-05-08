<?php

namespace Remarkety\Mgconnector\Controller\Adminhtml\Queue;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use Remarkety\Mgconnector\Model\QueueRepository;
use Remarkety\Mgconnector\Observer\EventMethods;
use Psr\Log\LoggerInterface;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Init action
     *
     */
	protected $resultPageFactory;
    protected $queueRepository;
    protected $eventMethods;
	public function __construct(
	    Context $context,
        PageFactory $resultPageFactory,
        QueueRepository $queueRepository,
        EventMethods $eventMethods
    ){

		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->queueRepository = $queueRepository;
		$this->eventMethods = $eventMethods;
	}

	public function execute()
	{
        $itemsDeleted = 0;
	    $item_ids = $this->getRequest()->getParam('queue');
	    foreach ($item_ids as $id){
	        try {
                $item = $this->queueRepository->getById($id);
                $this->queueRepository->delete($item);
                $itemsDeleted++;
            } catch (\Exception $ex){
	            $this->eventMethods->logError($ex);
            }

        }
        $this->getMessageManager()->addSuccessMessage(__('Total of %1 events(s) were deleted', $itemsDeleted));
        $this->_redirect('/*/queue');
	}
}
