<?php
namespace Remarkety\Mgconnector\Block\Frontend\Tracking;

use Magento\Store\Model\StoreManager;
use Magento\Framework\View\Element\Template\Context;
use \Remarkety\Mgconnector\Model\Webtracking;
use Magento\Customer\Model\Session;

class General extends Base
{
    public function __construct(Context $context, array $data = [], StoreManager $sManager, Webtracking $webtracking, Session $session)
    {
        parent::__construct($context, $data, $sManager, $webtracking, $session);
        $this->_isScopePrivate = true;
    }
}
