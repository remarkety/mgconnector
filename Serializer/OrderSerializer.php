<?php

namespace Remarkety\Mgconnector\Serializer;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order\Shipment;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Remarkety\Mgconnector\Helper\DataOverride;

class OrderSerializer
{

    use CheckSubscriberTrait;

    private $customerRepository;
    private $statusCollection;
    private $remarketyHelper;

    private $addressSerializer;
    private $customerSerializer;
    private $subscriber;
    private $dataOverride;

    public function __construct(
        CustomerRepository $customerRepository,
        \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection,
        \Remarkety\Mgconnector\Helper\Data $remarketyHelper,
        AddressSerializer $addressSerializer,
        CustomerSerializer $customerSerializer,
        Subscriber $subscriber,
        DataOverride $dataOverride
    )
    {
        $this->customerRepository = $customerRepository;
        $this->statusCollection = $statusCollection;
        $this->remarketyHelper = $remarketyHelper;
        $this->addressSerializer = $addressSerializer;
        $this->customerSerializer = $customerSerializer;
        $this->subscriber = $subscriber;
        $this->dataOverride = $dataOverride;
    }

    public function serialize(\Magento\Sales\Model\Order $order){
        //find order status
        $statusVal = $order->getStatus();
        $status = [
            'code' => 'unknown',
            'name' => 'unknown'
        ];
        if(!empty($statusVal)){
            $statusObj = $this->statusCollection->getItemByColumnValue('status', $statusVal);
            if(!empty($statusObj)){
                $status = [
                    'code' => $statusObj->getStatus(),
                    'name' => $statusObj->getLabel()
                ];
            }
        }
        /**
         * @var $items \Magento\Sales\Model\Order\Item[]
         */
        $items = $order->getAllItems();
        $line_items = [];
        foreach($items as $item){
            if ($item->getProductType() === Configurable::TYPE_CODE) {
                continue;
            }

            $parentItem = $item->getParentItem();
            if($parentItem && $parentItem->getProductType() == Configurable::TYPE_CODE){
                $price = (float)$parentItem->getPrice();
                $lineQty = (float)$parentItem->getQtyOrdered();
                $lineTax = (float)$parentItem->getTaxAmount();
                $quantity_refunded = $parentItem->getQtyRefunded();
                $quantity_shipped = $parentItem->getQtyShipped();
            } else {
                $price = (float)$item->getPrice();
                $lineQty = (float)$item->getQtyOrdered();
                $lineTax = (float)$item->getTaxAmount();
                $quantity_refunded = $item->getQtyRefunded();
                $quantity_shipped = $item->getQtyShipped();
            }

            $product = $item->getProduct();
            if($lineQty > 0 && $lineTax > 0){
                $itemTax = $lineTax / $lineQty;
            } else {
                $itemTax = 0;
            }
            $itemArr = [
                //'product_parent_id' => $rmCore->getProductParentId($item->getProduct()),
                'product_id' => $item->getProductId(),
                'sku' => $item->getSku(),
                'quantity' => $lineQty,
                'quantity_refunded' => $quantity_refunded,
                'quantity_shipped' => $quantity_shipped,
                'name' => $item->getName(),
                'title' => empty($product) ? $item->getName() : $product->getName(),
                'price' => $price,
                'tax_amount' => $itemTax,
                'url' => empty($product) ? null : $product->getProductUrl(),
                'images' => empty($product) ? [] : $this->remarketyHelper->getMediaGalleryImages($product)
            ];

            $line_items[] = $itemArr;
        }

        $created_at = new \DateTime($order->getCreatedAt());
        $updated_at = new \DateTime($order->getUpdatedAt());

        if(!$order->getCustomerIsGuest()){
            $customer = $this->customerRepository->getById($order->getCustomerId());
            $customerInfo = $this->customerSerializer->serialize($customer);
        } else {
            $billingAddress = $order->getBillingAddress();
            $customerInfo = [
                'accepts_marketing' => $this->checkSubscriber($order->getCustomerEmail(), null),
                'email' => $order->getCustomerEmail(),
                'title' => empty($billingAddress) ? null : $billingAddress->getPrefix(),
                'first_name' => empty($billingAddress) ? null : $billingAddress->getFirstname(),
                'last_name' => empty($billingAddress) ? null : $billingAddress->getLastname(),
                'created_at' => $created_at->format(\DateTime::ATOM ),
                'updated_at' => $created_at->format(\DateTime::ATOM ),
                'guest' => true,
                'default_address' => empty($billingAddress) ? null : $this->addressSerializer->serialize($billingAddress)
            ];
        }

        $shipping_lines = [];
        /**
         * @var $shipments Shipment[]
         */
        $shipments = $order->getShipmentsCollection();
        foreach($shipments as $shipment){
            /**
             * @var $trackings Shipment\Track[]
             */
            $trackings = $shipment->getAllTracks();
            foreach($trackings as $tracking){
                $shipping_lines[] = [
                    'tracking_number' => $tracking->getTrackNumber(),
                    'title' => $tracking->getTitle()
                ];
            }
        }

        $paymentMethodTitle = null;
        $payment = $order->getPayment();
        if(!empty($payment)){
            $method = $payment->getMethodInstance();
            if(!empty($method)){
                $paymentMethodTitle = $method->getTitle();
            }
        }

        $discount_codes = [];
        $coupon = $order->getCouponCode();
        if(!empty($coupon)){
            $discount_codes[] = [
                'code' => $coupon,
                'amount' => (float)$order->getDiscountAmount()
            ];
        }

        $data = [
            'id' => empty($order->getOriginalIncrementId()) ? $order->getIncrementId() : $order->getOriginalIncrementId(),
            'name' => $order->getIncrementId(),
            'created_at' => $created_at->format(\DateTime::ATOM ),
            'updated_at' => $updated_at->format(\DateTime::ATOM ),
            'currency' => $order->getOrderCurrencyCode(),
            'email' => $order->getCustomerEmail(),
            'discount_codes' => $discount_codes,
            'payment_method' => $paymentMethodTitle,
            'note' => $order->getCustomerNote(),
            'status' => $status,
            'state' => $order->getState(),
            'subtotal_price' => (float)$order->getSubtotal(),
            'total_discounts' => (float)$order->getDiscountAmount(),
            'total_price' => (float)$order->getGrandTotal(),
            'total_shipping' => (float)$order->getShippingAmount(),
            'total_tax' => (float)$order->getTaxAmount(),
            'customer' => $customerInfo,
            'shipping_address' => $this->addressSerializer->serialize($order->getShippingAddress()),
            'billing_address' => $this->addressSerializer->serialize($order->getBillingAddress()),
            'shipping_lines' => $shipping_lines,
            'line_items' => $line_items
        ];
        return $this->dataOverride->order($order, $data);
    }
}
