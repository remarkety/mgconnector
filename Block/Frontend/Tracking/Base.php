<?php
namespace Remarkety\Mgconnector\Block\Frontend\Tracking;

use Magento\Store\Model\StoreManager;
use Magento\Framework\View\Element\Template\Context;
use \Remarkety\Mgconnector\Model\Webtracking;
use \Magento\Customer\Model\Session;

class Base extends \Magento\Framework\View\Element\Template
{

    protected $_activeStore = null;
    protected $_webtracking;
    protected $_session;

    public function __construct(
        Context $context,
        array $data,
        StoreManager $sManager,
        Webtracking $webtracking,
        Session $session
    ) {
        parent::__construct($context, $data);
        $this->_activeStore = $sManager->getStore();
        $this->_webtracking = $webtracking;
        $this->_session = $session;
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
}
