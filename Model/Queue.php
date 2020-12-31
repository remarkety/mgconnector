<?php
namespace Remarkety\Mgconnector\Model;

class Queue extends \Magento\Framework\Model\AbstractModel implements \Remarkety\Mgconnector\Api\Data\QueueInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'remarkety_mgconnector_queue';

    protected function _construct()
    {
        $this->_init('Remarkety\Mgconnector\Model\ResourceModel\Queue');
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getQueueId()
    {
        return $this->getData('queue_id');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getEventType()
    {
        return $this->getData('event_type');
    }
    /**
     * Get URL Key
     *
     * @return array|null
     */
    public function getPayload()
    {
        return $this->getData('payload');
    }
    /**
     * Get URL Key
     *
     * @return int|null
     */
    public function getAttempts()
    {
        return $this->getData('attempts');
    }
    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getLastAttempt()
    {
        return $this->getData('last_attempt');
    }
    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getNextAttempt()
    {
        return $this->getData('next_attempt');
    }
    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->getData('status');
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
     * @param int $eventType
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
     * @param int $lastAttempt
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setLastAttempt($lastAttempt)
    {
        return $this->setData('last_attempt', $lastAttempt);
    }
    /**
     * Set ID
     *
     * @param int $nextAttempt
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setNextAttempt($nextAttempt)
    {
        return $this->setData('next_attempt', $nextAttempt);
    }
    /**
     * Set ID
     *
     * @param int $status
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setStatus($status)
    {
        return $this->setData('status', $status);
    }

    /**
     * @return string|null
     */
    public function getLastErrorMessage()
    {
        return $this->getData('last_error_message');
    }

    /**
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setLastErrorMessage($message)
    {
        return $this->setData('last_error_message', $message);
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * @param int $storeId
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }
}
