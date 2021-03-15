<?php
declare(strict_types=1);

namespace Remarkety\Mgconnector\Serializer;

use Magento\Catalog\Helper\Image;
use Magento\Checkout\Model\Session;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Remarkety\Mgconnector\Helper\Recovery;

class QuoteSerializer
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Image
     */
    private $imageHelper;

    /**
     * @var Recovery
     */
    private $recoveryHelper;

    /**
     * @param Session $checkoutSession
     * @param Image $imageHelper
     * @param Recovery $recoveryHelper
     */
    public function __construct(Session $checkoutSession, Image $imageHelper, Recovery $recoveryHelper)
    {
        $this->checkoutSession = $checkoutSession;
        $this->imageHelper = $imageHelper;
        $this->recoveryHelper = $recoveryHelper;
    }

    public function serialize(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $cart = [
            'abandoned_checkout_url' => $this->recoveryHelper->getCartRecoveryURL(
                $quote->getId(),
                $quote->getStore()->getId()
            ),
            'created_at'  => $quote->getCreatedAt(),
            'currency'    => $quote->getQuoteCurrencyCode(),
            'id'          => $quote->getId(),
            'line_items'  => $this->getQuoteItems(),
            'total_price' => floatval($quote->getGrandTotal()),
            'subtotal' => floatval($quote->getSubtotal()),
            'updated_at'  => $quote->getUpdatedAt()
        ];

        $address = $quote->getShippingAddress();
        if ($address) {
            $shipping_amount = $address->getShippingAmount();
            $cart['total_shipping'] = floatval($shipping_amount);
        }

        $coupon = $quote->getCouponCode();
        if (!empty($coupon)) {
            $coupon_discount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
            $cart['discount_codes'][] = [
                'code' => $coupon,
                'amount' => $coupon_discount
            ];
        }

        return $cart;
    }

    /**
     * @return array
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getQuoteItems(): array
    {
        $items = $this->checkoutSession->getQuote()->getItemsCollection();
        $lineItems = [];

        foreach ($items as $item) {
            if ($item->getProductType() === Configurable::TYPE_CODE) {
                continue;
            }

            $parentItem = $item->getParentItem();

            $price = $parentItem ? floatval($parentItem->getPriceInclTax()) : floatval($item->getPriceInclTax());
            $lineItems[] = [
                'product_id' => $item->getProductId(),
                'quantity' => $parentItem ? $parentItem->getQty() : $item->getQty(),
                'sku' => $item->getSku(),
                'title' => $item->getName(),
                'price' => $price,
                'taxable' => $item->getTaxPercent() > 0,
                'added_at' => $item->getCreatedAt(),
                'url' => $item->getProduct()->getProductUrl(),
                'image' => $this->imageHelper->init($item->getProduct(), 'product_thumbnail_image')->getUrl()
            ];
        }

        return $lineItems;
    }
}
