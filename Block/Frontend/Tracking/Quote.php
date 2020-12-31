<?php
namespace Remarkety\Mgconnector\Block\Frontend\Tracking;

use Magento\Checkout\Block\Cart;
use Magento\Checkout\Controller\Cart\Index;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use \Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManager;
use Magento\Framework\View\Element\Template\Context;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Helper\Recovery;
use \Remarkety\Mgconnector\Model\Webtracking;
use \Magento\Customer\Model\Session;
use Magento\Catalog\Model\Product as MageProduct;

class Quote extends \Magento\Framework\View\Element\Template
{
    private $quote;
    private $checkout_session;
    private $recovery_helper;
    private $media_path;
    private $config_helper;

    public function __construct(
        Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data,
        Recovery $recoveryHelper,
        ConfigHelper $configHelper
    ) {
        parent::__construct($context, $data);
        $this->checkout_session = $checkoutSession;
        $this->recovery_helper = $recoveryHelper;
        $this->config_helper = $configHelper;
    }

    public function isEventCartViewActivated()
    {
        return $this->config_helper->isEventCartViewEnabled();
    }

    public function getCart()
    {
        if (empty($this->getQuote()) || empty($this->getQuote()->getId())) {
            return null;
        }

        $cart = [
            'abandoned_checkout_url' => $this->recovery_helper->getCartRecoveryURL(
                $this->getQuote()->getId(),
                $this->getQuote()->getStore()->getId()
            ),
            'created_at'  => $this->getQuote()->getCreatedAt(),
            'currency'    => $this->getQuote()->getQuoteCurrencyCode(),
            'id'          => $this->getQuote()->getId(),
            'line_items'  => $this->getQuoteItems(),
            'total_price' => floatval($this->getQuote()->getGrandTotal()),
            'subtotal' => floatval($this->getQuote()->getSubtotal()),
            'updated_at'  => $this->getQuote()->getUpdatedAt()
        ];

        $address = $this->getQuote()->getShippingAddress();
        if ($address) {
            $shipping_amount = $address->getShippingAmount();
            $cart['total_shipping'] = floatval($shipping_amount);
        }

        $coupon = $this->getQuote()->getCouponCode();
        if (!empty($coupon)) {
            $coupon_discount = $this->getQuote()->getSubtotal() - $this->getQuote()->getSubtotalWithDiscount();
            $cart['discount_codes'][] = [
                'code' => $coupon,
                'amount' => $coupon_discount
            ];
        }

        return $cart;
    }

    private function getQuoteItems()
    {
        $items = $this->getQuote()->getItemsCollection();
        $line_items = [];

        foreach ($items as $item) {
            if ($item->getProductType() === Configurable::TYPE_CODE) {
                continue;
            }

            $parent_item = $item->getParentItem();

            $price = $parent_item ? floatval($parent_item->getPriceInclTax()) : floatval($item->getPriceInclTax());
            $line_items[] = [
                'product_id' => $item->getProductId(),
                'quantity'   => $parent_item ? $parent_item->getQty() : $item->getQty(),
                'sku'        => $item->getSku(),
                'title'      => $item->getName(),
                'price'      => $price,
                'taxable'    => $item->getTaxPercent() > 0,
                'added_at'   => $item->getCreatedAt(),
                'url'        => $item->getProduct()->getProductUrl(),
                'image'      => $this->getMediaPath() . $item->getProduct()->getThumbnail()
            ];
        }

        return $line_items;
    }

    private function getQuote()
    {
        if (!$this->quote) {
            $this->checkout_session->getQuote();
            if (!$this->hasData('quote')) {
                $this->setData('quote', $this->checkout_session->getQuote());
            }

            $this->quote = $this->_getData('quote');
        }

        return $this->quote;
    }

    private function getMediaPath()
    {
        if (!$this->media_path) {
            $this->media_path = $this
                    ->getQuote()
                    ->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        }

        return $this->media_path;
    }
}
