<?php


namespace Remarkety\Mgconnector\Observer;


use \Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\SalesRule\Model\CouponFactory;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Magento\Framework\App\Action\Context;

class TriggerCouponExpiration implements ObserverInterface
{
    private $coupon_factory;
    private $message_manager;
    private $response_factory;
    private $url;
    private $config_helper;
    private $context;

    public function __construct(
        ConfigHelper $configHelper,
        CouponFactory $couponFactory,
        ManagerInterface $messageManager,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        Context $context
    ) {
        $this->coupon_factory = $couponFactory;
        $this->message_manager = $messageManager;
        $this->response_factory = $responseFactory;
        $this->url = $url;
        $this->config_helper = $configHelper;
        $this->context = $context;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->config_helper->isAddonCouponExpiration()) {
            $controller = $observer->getControllerAction();
            $remove = $controller->getRequest()->getParam('remove');

            if (!$remove) {
                $coupon_code = $controller->getRequest()->getParam('coupon_code');

                if ($this->isCouponExpired($coupon_code)) {
                    $object_manager = $this->context->getObjectManager();
                    $escaper = $object_manager->get(\Magento\Framework\Escaper::class);
                    $message = __('The coupon code "%1" is not valid.', $escaper->escapeHtml($coupon_code));
                    $this->message_manager->addErrorMessage($message);
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
