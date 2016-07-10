<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Interface CouponInterface
 *
 * @api
 */
interface StoreSettingsAddressInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * @return string
     */
    public function getCountry();

    /**
     * @return string
     */
    public function getState();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string
     */
    public function getAddress_1();

    /**
     * @return string
     */
    public function getAddress_2();

    /**
     * @return string
     */
    public function getZip();
}