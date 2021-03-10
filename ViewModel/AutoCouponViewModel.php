<?php
declare(strict_types=1);

namespace Remarkety\Mgconnector\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;

class AutoCouponViewModel implements ArgumentInterface
{
    /**
     * @var ConfigHelper
     */
    private $helper;

    /**
     * @param ConfigHelper $helper
     */
    public function __construct(ConfigHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->helper->isCartAutoCouponEnabled();
    }
}
