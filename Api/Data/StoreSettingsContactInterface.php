<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Interface CouponInterface
 *
 * @api
 */
interface StoreSettingsContactInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getPhone();
}