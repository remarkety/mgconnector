<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Interface CouponInterface
 *
 * @api
 */
interface UnsubscribeResponseInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @return string
     */
    public function getMessage();
}
