<?php
/**
 * Created by PhpStorm.
 * User: kostya
 * Date: 11/22/18
 * Time: 2:59 PM
 */

namespace Remarkety\Mgconnector\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\QuoteFactory;

class Recovery extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $scopeConfig;
    protected $storeManager;
    protected $quoteFactory;

    /**
     * Recovery constructor.
     *
     * @param Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param QuoteFactory $quoteFactory
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        QuoteFactory $quoteFactory
    ) {
        $this->storeManager = $storeManager;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context);
    }

    /**
     * Get magento config array
     *
     * @param $config_path
     *
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Create url to recovery cart
     *
     * @param      $cartId
     * @param null $storeId
     *
     * @return string
     */
    public function getCartRecoveryURL($cartId, $storeId = null)
    {
        $id = $this->encodeQuoteId($cartId);
        if ($storeId) {
            $url = $this->storeManager->getStore($storeId)->getBaseUrl() . "mgconnector/frontend/recovery/cart/$id";
        } else {
            $url = $this->storeManager->getStore()->getBaseUrl() . "mgconnector/frontend/recovery/cart/$id";
        }

        return $url;
    }

    /**
     * Restore quote
     * remove customer and all shipping and payment data
     *
     * @param $old_quote
     * @return int
     */
    public function quoteRestore($old_quote)
    {
        $quote = $this->quoteFactory->create();
        $quote->merge($old_quote);
        $quote->setItemsCount($old_quote->getItemsCount());
        $quote->setItemsQty($old_quote->getItemsQty());
        $quote->setStoreId($old_quote->getStoreId());
        $quote->collectTotals();
        $quote->save();
        return $quote->getId();
    }

    /**
     * Decodes signed cart ids from urls
     * @param $hashed_id
     * @return bool|int
     */
    public function decodeQuoteId($hashed_id)
    {
        $id = null;
        $parts = base64_decode($hashed_id);
        if (!empty($parts)) {
            $split = explode(':', $parts);
            if (count($split) == 2) {
                $cart_id = $split[0];
                if (is_numeric($cart_id)) {
                    $sign = sha1($cart_id . '.' . $this->getApiKey());
                    $sign_from_request = $split[1];
                    if ($sign === $sign_from_request) {
                        return (int)$cart_id;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Generates signed cart id for urls
     * @param $id
     * @return string
     */
    private function encodeQuoteId($id)
    {
        $sign = sha1($id . '.' . $this->getApiKey());

        return base64_encode($id . ':' . $sign);
    }

    /**
     * Get remarkety api key from config
     *
     * @return mixed
     */
    private function getApiKey()
    {
        return $this->getConfig('remarkety/mgconnector/api_key');
    }
}
