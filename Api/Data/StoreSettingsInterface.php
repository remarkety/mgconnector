<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Interface CouponInterface
 *
 * @api
 */
interface StoreSettingsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * @return string
     */
    public function getStore_front_url();

    /**
     * @return string
     */
    public function getDomain();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLogo_url();

    /**
     * @return \Remarkety\Mgconnector\Api\Data\StoreSettingsContactInterface
     */
    public function getContact_info();

    /**
     * @return string
     */
    public function getTimezone();

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @return string
     */
    public function getLocale();

    /**
     * @return \Remarkety\Mgconnector\Api\Data\StoreSettingsAddressInterface
     */
    public function getAddress();

    /**
     * @return mixed[]
     */
    public function getOrder_statuses();
}
