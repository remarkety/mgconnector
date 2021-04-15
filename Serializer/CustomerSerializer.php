<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 4/26/17
 * Time: 3:00 PM
 */

namespace Remarkety\Mgconnector\Serializer;

use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\App\RequestInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use Remarkety\Mgconnector\Helper\Data;
use Remarkety\Mgconnector\Helper\DataOverride;

class CustomerSerializer
{
    use CheckSubscriberTrait;

    /**
     * @var SubscriberFactory
     */
    private $subscriber;

    /**
     * @var AddressSerializer
     */
    private $addressSerializer;

    /**
     * @var CustomerGroupRepository
     */
    private $customerGroupRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    private $pos_id_attribute_code;

    /**
     * @var DataOverride
     */
    private $dataOverride;

    /**
     * @var Data
     */
    private $dataHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        SubscriberFactory $subscriber,
        AddressSerializer $addressSerializer,
        CustomerGroupRepository $customerGroupRepository,
        RequestInterface $request,
        LoggerInterface $logger,
        ConfigHelper $configHelper,
        DataOverride $dataOverride,
        Data $dataHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->subscriber = $subscriber;
        $this->addressSerializer = $addressSerializer;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->request = $request;
        $this->logger = $logger;
        $this->configHelper = $configHelper;
        $this->pos_id_attribute_code = $configHelper->getPOSAttributeCode();
        $this->dataOverride = $dataOverride;
        $this->dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
    }

    public function serialize(Customer $customer)
    {
        if ($this->request->getParam('is_subscribed', false)) {
            //check if waiting for email approval
            $needsConfirmation = $this->configHelper->customerPendingConfirmation($customer);
            if ($needsConfirmation) {
                //if needs approval, the email might already be subscribed
                $subscribed = $this->checkSubscriber($customer->getEmail(), $customer->getId(), $this->storeManager->getWebsite()->getId());
            } else {
                $subscribed = false;
            }
        } else {
            $subscribed = $this->checkSubscriber($customer->getEmail(), $customer->getId(), $this->storeManager->getWebsite()->getId());
        }
        $created_at = new \DateTime($customer->getCreatedAt());
        $updated_at = new \DateTime($customer->getUpdatedAt());

        $groups = [];
        if (!empty($customer->getGroupId())) {
            try {
                $group = $this->customerGroupRepository->getById($customer->getGroupId());
                if ($group) {
                    $groups[] = [
                        'id' => $group->getId(),
                        'name' => $group->getCode(),
                    ];
                }
            } catch (\Exception $ex) {
                $this->logError($ex);
            }
        }
        $gender = null;
        switch ($customer->getGender()) {
            case 1:
                $gender = 'male';
                break;
            case 2:
                $gender = 'female';
                break;
        }

        $address = $this->dataHelper->getCustomerAddresses($customer);
        $pos_id = $this->getPosId($customer);

        $customerInfo = [
            'id' => (int)$customer->getId(),
            'email' => $customer->getEmail(),
            'accepts_marketing' => $subscribed,
            'title' => $customer->getPrefix(),
            'first_name' => $customer->getFirstname(),
            'last_name' => $customer->getLastname(),
            'created_at' => $created_at->format(\DateTime::ATOM),
            'updated_at' => $updated_at->format(\DateTime::ATOM),
            'guest' => false,
            'default_address' => $address,
            'groups' => $groups,
            'gender' => $gender,
            'birthdate' => $customer->getDob(),
            'pos_id' => $pos_id
        ];

        return $this->dataOverride->customer($customer, $customerInfo);
    }

    protected function logError(\Exception $exception)
    {
        $this->logger->error("Remarkety:" . self::class . " - " . $exception->getMessage(), [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    protected function getPosId(Customer $customer)
    {
        $pos_id = null;
        if (!empty($this->pos_id_attribute_code)) {
            $attr = $customer->getCustomAttribute($this->pos_id_attribute_code);
            if ($attr) {
                $attr_val = $attr->getValue();
                $pos_id = !empty($attr_val) ? $attr_val : null;
            } else {
                $pos_get_method = "get" . Data::toCamelCase($this->pos_id_attribute_code, true);
                if (method_exists($customer, $pos_get_method)) {
                    $pos_id = $customer->$pos_get_method();
                }
            }
        }
        return $pos_id;
    }
}
