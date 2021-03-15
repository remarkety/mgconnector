<?php
declare(strict_types=1);

namespace Remarkety\Mgconnector\ViewModel;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;

class AddToCartEventViewModel implements ArgumentInterface
{
    private const ADD_TO_CART_EVENT_URL = 'mgconnector/ajax/addEvent';

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ConfigHelper $configHelper
     * @param UrlInterface $urlBuilder
     */
    public function __construct(ConfigHelper $configHelper, UrlInterface $urlBuilder)
    {
        $this->configHelper = $configHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->configHelper->isEventAddToCartViewEnabled();
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->urlBuilder->getUrl(static::ADD_TO_CART_EVENT_URL);
    }
}
