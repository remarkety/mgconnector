<?php

namespace Remarkety\Mgconnector\Controller\Adminhtml\Queue;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use Remarkety\Mgconnector\Model\QueueRepository;
use Remarkety\Mgconnector\Observer\EventMethods;
use Psr\Log\LoggerInterface;

class Resend extends \Magento\Backend\App\Action
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
    ) {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->queueRepository = $queueRepository;
        $this->eventMethods = $eventMethods;
    }

    public function execute()
    {
        $itemsSent = 0;
        $item_ids = $this->getRequest()->getParam('queue');
        foreach ($item_ids as $id) {
            try {
                $item = $this->queueRepository->getById($id);
                if ($this->eventMethods->makeRequest(
                    $item->getEventType(),
                    json_decode($item->getPayload(), true),
                    $item->getStoreId(),
                    $item->getAttempts(),
                    $item->getQueueId()
                )) {
                    $itemsSent++;
                    $this->queueRepository->delete($item);
                }
            } catch (\Exception $ex) {
                $this->eventMethods->logError($ex);
            }

        }
        $this->getMessageManager()->addSuccessMessage(__('Total of %1 events(s) were resent', $itemsSent));
        $this->_redirect('/*/queue');
    }
}
