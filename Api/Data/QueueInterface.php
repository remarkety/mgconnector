<?php
namespace Remarkety\Mgconnector\Api\Data;

interface QueueInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getQueueId();

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getEventType();

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getPayload();

    /**
     * Get URL Key
     *
     * @return int|null
     */
    public function getAttempts();

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getLastAttempt();
    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getNextAttempt();
    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getStatus();
    /**
     * Set ID
     *
     * @param int $queueId
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setQueueId($queueId);
    /**
     * Set ID
     *
     * @param string $eventType
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setEventType($eventType);
    /**
     * Set ID
     *
     * @param array $payload
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setPayload($payload);
    /**
     * Set ID
     *
     * @param int $attempts
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setAttempts($attempts);
    /**
     * Set ID
     *
     * @param string $lastAttempt
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setLastAttempt($lastAttempt);
    /**
     * Set ID
     *
     * @param string $nextAttempt
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setNextAttempt($nextAttempt);
    /**
     * Set ID
     *
     * @param string $status
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getLastErrorMessage();

    /**
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setLastErrorMessage($message);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return \Remarkety\Mgconnector\Api\Data\QueueInterface
     */
    public function setStoreId($storeId);
}
