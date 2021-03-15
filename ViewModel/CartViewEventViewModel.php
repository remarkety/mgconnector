<?php
declare(strict_types=1);

namespace Remarkety\Mgconnector\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Serializer\QuoteSerializer;

class CartViewEventViewModel implements ArgumentInterface
{
    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var QuoteSerializer
     */
    private $quoteSerializer;

    /**
     * @param ConfigHelper $configHelper
     * @param QuoteSerializer $quoteSerializer
     */
    public function __construct(ConfigHelper $configHelper, QuoteSerializer $quoteSerializer)
    {
        $this->configHelper = $configHelper;
        $this->quoteSerializer = $quoteSerializer;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->configHelper->isEventCartViewEnabled();
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return json_encode($this->quoteSerializer->serialize());
    }
}
