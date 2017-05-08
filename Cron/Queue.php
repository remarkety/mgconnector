<?php

namespace Remarkety\Mgconnector\Cron;

use Psr\Log\LoggerInterface;
use Remarkety\Mgconnector\Model\Queue as QueueModel;
use Remarkety\Mgconnector\Model\QueueRepository;
use Remarkety\Mgconnector\Observer\EventMethods;

class Queue
{

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var QueueModel
     */
    protected $queueModel;

    /**
     * @var EventMethods
     */
    protected $eventMethods;

    protected $queueRepository;

    /**
     * Queue constructor.
     *
     * @param LoggerInterface $logger
     * @param QueueModel $queueModel
     * @param EventMethods $eventMethods
     * @param QueueRepository $queueRepository
     */
    public function __construct(
        LoggerInterface $logger,
        QueueModel      $queueModel,
        EventMethods    $eventMethods,
        QueueRepository $queueRepository
    ) {
        $this->logger       = $logger;
        $this->queueModel   = $queueModel;
        $this->eventMethods = $eventMethods;
        $this->queueRepository = $queueRepository;
    }

    /**
     * Resend request to remarkety.
     *
     * @param QueueModel $queueItems
     * @param bool       $resetAttempts
     *
     * @return int
     */
    protected function resend($queueItems,$resetAttempts = false) {
        $sent = 0;
        foreach($queueItems as $queue) {
            $result = $this->eventMethods->makeRequest(
                $queue->getEventType(),
                json_decode($queue->getPayload(), true),
                $queue->getStoreId(),
                $resetAttempts ? 0 : $queue->getAttempts(),
                $queue->getId()
            );
            if($result) {
                $this->queueRepository->deleteById($queue->getId());
                $sent++;
            }
        }
        return $sent;
    }

    /**
     * Run cron action.
     *
     * @return $this
     */
    public function execute()
    {
        try {
            $collection = $this->queueModel->getCollection();
            $nextAttempt = date("Y-m-d H:i:s");
            $collection
            ->getSelect()
            ->where('next_attempt <= ?', $nextAttempt)
            ->where('status = 1')
            ->order('main_table.next_attempt asc');
            $this->resend($collection);
        } catch(\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        return $this;
    }

}
