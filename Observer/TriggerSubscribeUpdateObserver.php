<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Customer\Model\Session;
use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Checkout\Model\Session as CheckoutSession;

class TriggerSubscribeUpdateObserver extends EventMethods implements ObserverInterface {

    protected $_subscriber = null;
    protected $_checkoutSession;
//    protected $_coreRegistry;

    public function __construct(
        Session $customerSession,
        CheckoutSession $CheckoutSession,
        Registry $registry,
        Subscriber $subscriber,
        Group $customerGroupModel,
        Queue $remarketyQueue,
        Store $store,
        ScopeConfigInterface $scopeConfig,
        StoreManager $sManager
    ){
        parent::__construct($registry, $subscriber, $customerGroupModel, $remarketyQueue, $store, $scopeConfig);
        $this->session = $customerSession;
        $this->_checkoutSession = $CheckoutSession;
        $this->_store = $sManager->getStore();
    }
    /**
     * Apply catalog price rules to product in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer){
        $this->_subscriber = $observer->getEvent()->getSubscriber();

        $this->_coreRegistry->register('subscriber_object_data_observer', $this->_subscriber);

        if($this->_subscriber->getId() && !$this->session->isLoggedIn()) {
            if($this->_subscriber->getCustomerId() && $this->_coreRegistry->registry('remarkety_customer_save_observer_executed_'.$this->_subscriber->getCustomerId())) {
                return $this;
            }
            if ($this->_coreRegistry->registry('remarkety_subscriber_deleted'))
                return $this;
            $this->makeRequest('customers/create', $this->_prepareCustomerSubscribtionUpdateData());

            $email = $this->_subscriber->getSubscriberEmail();
            if(!empty($email)){
                //for webtracking use
                $this->session->setSubscriberEmail($email);
                //add email to cart
                $cart = $this->_checkoutSession->getQuote();
                if($cart && !is_null($cart->getId()) && is_null($cart->getCustomerEmail())){
                    $cart->setCustomerEmail($email)->save();
                }
            }
        }

        return $this;
    }
}