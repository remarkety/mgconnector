<?php

namespace Remarkety\Mgconnector\Serializer;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Newsletter\Model\Subscriber;
use Magento\Sales\Model\Order\Shipment;

class OrderSerializer
{

    private $customerRepository;
    private $statusCollection;
    private $remarketyHelper;

    private $addressSerializer;
    private $customerSerializer;
    private $subscriber;

    public function __construct(
        CustomerRepository $customerRepository,
        \Magento\Sales\Model\ResourceModel\Order\Status\Collection $statusCollection,
        \Remarkety\Mgconnector\Helper\Data $remarketyHelper,
        AddressSerializer $addressSerializer,
        CustomerSerializer $customerSerializer,
        Subscriber $subscriber
    )
    {
        $this->customerRepository = $customerRepository;
        $this->statusCollection = $statusCollection;
        $this->remarketyHelper = $remarketyHelper;
        $this->addressSerializer = $addressSerializer;
        $this->customerSerializer = $customerSerializer;
        $this->subscriber = $subscriber;
    }

    public function serialize(\Magento\Sales\Model\Order $order){
        $status = $this->statusCollection->getItemByColumnValue('status', $order->getStatus());
        /**
         * @var $items \Magento\Sales\Model\Order\Item[]
         */
        $items = $order->getAllVisibleItems();
        $line_items = [];
        foreach($items as $item){
            $product = $item->getProduct();
            $lineTax = (float)$item->getTaxAmount();
            $lineQty = (float)$item->getQtyOrdered();
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
                'quantity_refunded' => $item->getQtyRefunded(),
                'quantity_shipped' => $item->getQtyShipped(),
                'name' => $item->getName(),
                'title' => empty($product) ? $item->getName() : $product->getName(),
                'price' => (float)$item->getPrice(),
                'tax_amount' => $itemTax,
                'url' => empty($product) ? null : $product->getProductUrl(),
                'images' => empty($product) ? [] : $this->remarketyHelper->getMediaGalleryImages($product)
            ];

            $line_items[] = $itemArr;
        }

        $created_at = new \DateTime($order->getCreatedAt());
        $updated_at = new \DateTime($order->getUpdatedAt());

        $customerId = $order->getCustomerId();
        if(!$order->getCustomerIsGuest()){
            $customer = $this->customerRepository->getById($order->getCustomerId());
            $customerInfo = $this->customerSerializer->serialize($customer);
        } else {
            $checkSubscriber = $this->subscriber->loadByEmail($order->getCustomerEmail());

            $customerInfo = [
                'accepts_marketing' => $checkSubscriber->isSubscribed(),
                'email' => $order->getCustomerEmail(),
                'title' => $order->getBillingAddress()->getPrefix(),
                'first_name' => $order->getBillingAddress()->getFirstname(),
                'last_name' => $order->getBillingAddress()->getLastname(),
                'created_at' => $created_at->format(\DateTime::ATOM ),
                'updated_at' => $created_at->format(\DateTime::ATOM ),
                'guest' => true,
                'default_address' => $this->addressSerializer->serialize($order->getBillingAddress())
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

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $paymentMethodTitle = $method->getTitle();

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
            'status' => [
                'code' => $status->getStatus(),
                'name' => $status->getLabel()
            ],
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
        return $data;
    }
}
