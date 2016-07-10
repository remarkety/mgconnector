<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Interface CouponInterface
 *
 * @api
 */
interface QuoteInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);
    /**
     * @return string|null
     */
    public function getItemsCount();

    /**
     * @param string $itemsCount
     * @return $this
     */
    public function setItemsCount($itemsCount);
    /**
     * @return string|null
     */
    public function getCustomerFirstname();

    /**
     * @param string $customerFirstname
     * @return $this
     */
    public function setCustomerFirstname($customerFirstname);

    /**
     * @return string|null
     */
    public function getCustomerLastname();

    /**
     * @param string $customerLastname
     * @return $this
     */
    public function setCustomerLastname($customerLastname);

    /**
     * @return string|null
     */
    public function getCustomerEmail();

    /**
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail($customerEmail);
    /**
     * @return string|null
     */
    public function getBaseCurrencyCode();

    /**
     * @param string $baseCurrencyCode
     * @return $this
     */
    public function setBaseCurrencyCode($baseCurrencyCode);
    /**
     * @return int|null
     */
    public function getSubtotal();

    /**
     * @param int $subtotal
     * @return $this
     */
    public function setSubtotal($subtotal);
    /**
     * @return int|null
     */
    public function getGrandTotal();

    /**
     * @param int $grandtotal
     * @return $this
     */
    public function setGrandTotal($grandtotal);
}