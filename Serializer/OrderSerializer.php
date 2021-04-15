<?php

namespace Remarkety\Mgconnector\Serializer;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Sales\Model\ResourceModel\Order\Status\Collection;
use Magento\Store\Model\StoreManagerInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Helper\Data;
use Remarkety\Mgconnector\Helper\DataOverride;

class OrderSerializer
{
    use CheckSubscriberTrait;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var Collection
     */
    private $statusCollection;

    /**
     * @var Data
     */
    private $remarketyHelper;

    /**
     * @var AddressSerializer
     */
    private $addressSerializer;

    /**
     * @var CustomerSerializer
     */
    private $customerSerializer;

    /**
     * @var SubscriberFactory
     */
    private $subscriber;

    /**
     * @var DataOverride
     */
    private $dataOverride;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CustomerRepository $customerRepository
     * @param Collection $statusCollection
     * @param Data $remarketyHelper
     * @param AddressSerializer $addressSerializer
     * @param CustomerSerializer $customerSerializer
     * @param SubscriberFactory $subscriber
     * @param DataOverride $dataOverride
     * @param ConfigHelper $configHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerRepository $customerRepository,
        Collection $statusCollection,
        Data $remarketyHelper,
        AddressSerializer $addressSerializer,
        CustomerSerializer $customerSerializer,
        SubscriberFactory $subscriber,
        DataOverride $dataOverride,
        ConfigHelper $configHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->statusCollection = $statusCollection;
        $this->remarketyHelper = $remarketyHelper;
        $this->addressSerializer = $addressSerializer;
        $this->customerSerializer = $customerSerializer;
        $this->subscriber = $subscriber;
        $this->dataOverride = $dataOverride;
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
    }

    public function serialize(OrderInterface $order)
    {
        //find order status
        $statusVal = $order->getStatus();
        $status = [
            'code' => 'unknown',
            'name' => 'unknown'
        ];
        if (!empty($statusVal)) {
            $statusObj = $this->statusCollection->getItemByColumnValue('status', $statusVal);
            if (!empty($statusObj)) {
                $status = [
                    'code' => $statusObj->getStatus(),
                    'name' => $statusObj->getLabel()
                ];
            }
        }

        $items = $order->getAllItems();
        $line_items = [];
        foreach ($items as $item) {
            if ($item->getProductType() === Configurable::TYPE_CODE) {
                continue;
            }

            $parentItem = $item->getParentItem();
            if ($parentItem && $parentItem->getProductType() == Configurable::TYPE_CODE) {
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
            if ($lineQty > 0 && $lineTax > 0) {
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

        if (!$order->getCustomerIsGuest()) {
            $customer = $this->customerRepository->getById($order->getCustomerId());
            $customerInfo = $this->customerSerializer->serialize($customer);
        } else {
            if ($this->configHelper->getCustomerAddressType() === ConfigHelper::CUSTOMER_ADDRESS_BILLING) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            $customerInfo = [
                'accepts_marketing' => $this->checkSubscriber(
                    $order->getCustomerEmail(),
                    null,
                    $this->storeManager->getWebsite()->getId()
                ),
                'email' => $order->getCustomerEmail(),
                'title' => empty($address) ? null : $address->getPrefix(),
                'first_name' => empty($address) ? null : $address->getFirstname(),
                'last_name' => empty($address) ? null : $address->getLastname(),
                'created_at' => $created_at->format(\DateTime::ATOM),
                'updated_at' => $created_at->format(\DateTime::ATOM),
                'guest' => true,
                'default_address' => empty($address) ? null : $this->addressSerializer->serialize($address)
            ];
        }

        $shipping_lines = [];
        /**
         * @var $shipments Shipment[]
         */
        $shipments = $order->getShipmentsCollection();
        foreach ($shipments as $shipment) {
            /**
             * @var $trackings Shipment\Track[]
             */
            $trackings = $shipment->getAllTracks();
            foreach ($trackings as $tracking) {
                $shipping_lines[] = [
                    'tracking_number' => $tracking->getTrackNumber(),
                    'title' => $tracking->getTitle()
                ];
            }
        }

        $paymentMethodTitle = null;
        $payment = $order->getPayment();
        if (!empty($payment)) {
            $method = $payment->getMethodInstance();
            if (!empty($method)) {
                $paymentMethodTitle = $method->getTitle();
            }
        }

        $discount_codes = [];
        $coupon = $order->getCouponCode();
        if (!empty($coupon)) {
            $discount_codes[] = [
                'code' => $coupon,
                'amount' => (float)$order->getDiscountAmount()
            ];
        }

        $data = [
            'id' => empty($order->getOriginalIncrementId()) ? $order->getIncrementId() : $order->getOriginalIncrementId(),
            'name' => $order->getIncrementId(),
            'created_at' => $created_at->format(\DateTime::ATOM),
            'updated_at' => $updated_at->format(\DateTime::ATOM),
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
