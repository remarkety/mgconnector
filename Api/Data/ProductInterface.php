<?php
namespace Remarkety\Mgconnector\Api\Data;

/**
 * Interface CouponInterface
 *
 * @api
 */
interface ProductInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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
    public function getSku();

    /**
     * @param $sku
     * @return $this
     */
    public function setSku($sku);
    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param $name
     * @return $this
     */
    public function setName($name);
}
