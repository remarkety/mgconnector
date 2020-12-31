<?php


namespace Remarkety\Mgconnector\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class DataOverride
{
    /**
     * @param Customer $customer
     * @param $data
     * @return mixed
     */
    public function customer($customer, $data)
    {
        return $data;
    }

    public function newsletter($data)
    {
        return $data;
    }

    /**
     * @param Quote $quote
     * @param $data
     * @return mixed
     */
    public function cart($quote, $data)
    {
        return $data;
    }

    /**
     * @param Order $order
     * @param $data
     * @return mixed
     */
    public function order($order, $data)
    {
        return $data;
    }

    /**
     * @param ProductInterface $product
     * @param $data
     * @return mixed
     */
    public function product($product, $data)
    {
        return $data;
    }
}
