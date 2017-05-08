<?php

namespace Remarkety\Mgconnector\Model\Data;

/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 4/27/17
 * Time: 3:41 PM
 */
class Queue extends \Magento\Framework\Api\AbstractExtensibleObject implements \Remarkety\Mgconnector\Api\Data\QueueInterface
{

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getQueueId()
    {
        return $this->_get('queue_id');
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getEventType()
    {
        return $this->_get('event_type');
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getPayload()
    {
        return $this->_get('payload');
    }

    /**
     * Get URL Key
     *
     * @return int|null
     */
    public function getAttempts()
    {
        return $this->_get('attempts');
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getLastAttempt()
    {
        return $this->_get('last_attempt');
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getNextAttempt()
    {
        return $this->_get('next_attempt');
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get('status');
    }

    /**
     * Set ID
     *
     * @param int $queueId
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setQueueId($queueId)
    {
        return $this->setData('queue_id', $queueId);
    }

    /**
     * Set ID
     *
     * @param string $eventType
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setEventType($eventType)
    {
        return $this->setData('event_type', $eventType);
    }

    /**
     * Set ID
     *
     * @param array $payload
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setPayload($payload)
    {
        return $this->setData('payload', $payload);
    }

    /**
     * Set ID
     *
     * @param int $attempts
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setAttempts($attempts)
    {
        return $this->setData('attempts', $attempts);
    }

    /**
     * Set ID
     *
     * @param string $lastAttempt
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setLastAttempt($lastAttempt)
    {
        return $this->setData('last_attempt', $lastAttempt);
    }

    /**
     * Set ID
     *
     * @param string $nextAttempt
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setNextAttempt($nextAttempt)
    {
        return $this->setData('next_attempt', $nextAttempt);
    }

    /**
     * Set ID
     *
     * @param string $status
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }

    /**
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_get('store_id');
    }

    /**
     * @param $storeId
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    /**
     * @return string|null
     */
    public function getLastErrorMessage()
    {
        return $this->_get('last_error_message');
    }

    /**
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setLastErrorMessage($message)
    {
        return $this->setData('last_error_message', $message);
    }
}
