<?php


namespace Remarkety\Mgconnector\Observer;


use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Group;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\SalesRule\Model\CouponFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Model\QueueRepository;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CustomerSerializer;
use Remarkety\Mgconnector\Serializer\OrderSerializer;
use Remarkety\Mgconnector\Serializer\ProductSerializer;

class TriggerCouponExpiration extends EventMethods implements ObserverInterface
{
    private $coupon_factory;
    private $message_manager;
    private $response_factory;
    private $url;
    private $config_helper;

    public function __construct(
        LoggerInterface $logger,
        Registry $coreRegistry,
        Subscriber $subscriber,
        Group $customerGroupModel,
        QueueRepository $remarketyQueueRepo,
        \Remarkety\Mgconnector\Model\QueueFactory $queueFactory,
        Store $store,
        ScopeConfigInterface $scopeConfig,
        OrderSerializer $orderSerializer,
        CustomerSerializer $customerSerializer,
        AddressSerializer $addressSerializer,
        ConfigHelper $configHelper,
        ProductSerializer $productSerializer,
        Http $request,
        CustomerRepository $customerRepository,
        CustomerRegistry $customerRegistry,
        StoreManager $storeManager,
        CouponFactory $couponFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ResponseFactory $responseFactory,
        UrlInterface $url
    ) {
        parent::__construct($logger, $coreRegistry, $subscriber, $customerGroupModel, $remarketyQueueRepo,
            $queueFactory, $store, $scopeConfig, $orderSerializer, $customerSerializer, $addressSerializer,
            $configHelper, $productSerializer, $request, $customerRepository, $customerRegistry, $storeManager);

        $this->coupon_factory = $couponFactory;
        $this->message_manager = $messageManager;
        $this->response_factory = $responseFactory;
        $this->url = $url;
        $this->config_helper = $configHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config_helper->isAddonCouponExpiration()) {
            $controller = $observer->getControllerAction();
            $remove = $controller->getRequest()->getParam('remove');

            if (!$remove) {
                $coupon_code = $controller->getRequest()->getParam('coupon_code');

                if ($this->isCouponExpired($coupon_code)) {

                    $message = "The coupon code $coupon_code is not valid.";
                    $this->message_manager->addError($message);
                    $cartUrl = $this->url->getUrl('checkout/cart/index');
                    $this->response_factory->create()->setRedirect($cartUrl)->sendResponse();
                    exit;
                }
            }
        }
    }

    /**
     * This coupon expirated
     *
     * @param $coupon_code
     *
     * @return bool
     * @throws \Exception
     */
    private function isCouponExpired($coupon_code) {
        $is_expired = false;
        $coupon = $this->loadCoupon($coupon_code);
        $expiration_date = $coupon->getExpirationDate();

        if ($expiration_date) {
            $expiration = new \DateTime($expiration_date);
            $date_now = new \DateTime();

            if ($expiration < $date_now) {
                $is_expired = true;
            }
        }

        return $is_expired;
    }

    /**
     * Load coupon by coupon code
     *
     * @param $coupon_code
     *
     * @return \Magento\SalesRule\Model\Coupon
     */
    private function loadCoupon($coupon_code) {
        $coupon = $this->coupon_factory->create();
        $coupon->load($coupon_code, 'code');

        return $coupon;
    }
}
