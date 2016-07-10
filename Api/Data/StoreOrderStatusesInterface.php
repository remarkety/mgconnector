<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Interface CouponInterface
 *
 * @api
 */
interface StoreOrderStatusesInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * @return string|null
     */
    public function getLabel();

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);
}