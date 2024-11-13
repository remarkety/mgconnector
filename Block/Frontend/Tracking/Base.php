<?php
namespace Remarkety\Mgconnector\Block\Frontend\Tracking;

use Magento\Store\Model\StoreManager;
use Magento\Framework\View\Element\Template\Context;
use \Remarkety\Mgconnector\Helper\ConfigHelper;
use \Remarkety\Mgconnector\Model\Webtracking;
use \Magento\Customer\Model\Session;

class Base extends \Magento\Framework\View\Element\Template
{

    protected $_activeStore = null;
    protected $_webtracking;
    protected $_session;
    protected $config_helper;

    public function __construct(
        Context $context,
        array $data,
        StoreManager $sManager,
        Webtracking $webtracking,
        Session $session,
        ConfigHelper $config_helper
    ) {
        parent::__construct($context, $data);
        $this->_activeStore = $sManager->getStore();
        $this->_webtracking = $webtracking;
        $this->_session = $session;
        $this->config_helper = $config_helper;
    }

    public function isWebtrackingActivated()
    {
        return ($this->getRemarketyPublicId() !== false);
    }

    public function getRemarketyPublicId()
    {
        return $this->_webtracking->getRemarketyPublicId();
    }

    public function getCustomer()
    {
        return $this->_session->getCustomer();
    }

    public function getEmail()
    {

        if ($this->_session->isLoggedIn()) {
            return $this->getCustomer()->getEmail();
        }
        $email = $this->_session->getSubscriberEmail();
        return empty($email) ? false : $email;
    }

    private function getConfig($config)
    {
        return $this->config_helper->getValue($config);
    }

    public function isPopupEnabled()
    {
        return $this->config_helper->getValue(ConfigHelper::POPUP_ENABLED) == 1;
    }
}
