<?php

namespace Remarkety\Mgconnector\Cron;

use Psr\Log\LoggerInterface;
use Remarkety\Mgconnector\Model\Queue as QueueModel;
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

    /**
     * Queue constructor.
     *
     * @param LoggerInterface $logger
     * @param QueueModel      $queueModel
     * @param EventMethods    $eventMethods
     */
    public function __construct(
        LoggerInterface $logger,
        QueueModel      $queueModel,
        EventMethods    $eventMethods
    ) {
        $this->logger       = $logger;
        $this->queueModel   = $queueModel;
        $this->eventMethods = $eventMethods;
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
                unserialize($queue->getPayload()),
                $resetAttempts ? 1 : ($queue->getAttempts()+1),
                $queue->getId()
            );
            if($result) {
                $this->queueModel
                    ->load($queue->getId())
                    ->delete();
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