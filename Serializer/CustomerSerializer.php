<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 4/26/17
 * Time: 3:00 PM
 */

namespace Remarkety\Mgconnector\Serializer;


use Magento\Framework\App\RequestInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\Customer\Api\GroupRepositoryInterface as CustomerGroupRepository;

class CustomerSerializer
{

    private $subscriber;
    private $addressSerializer;
    private $customerGroupRepository;
    private $request;
    public function __construct(
        Subscriber $subscriber,
        AddressSerializer $addressSerializer,
        CustomerGroupRepository $customerGroupRepository,
        RequestInterface $request
    )
    {
        $this->subscriber = $subscriber;
        $this->addressSerializer = $addressSerializer;
        $this->customerGroupRepository = $customerGroupRepository;
        $this->request = $request;
    }
    public function serialize(\Magento\Customer\Api\Data\CustomerInterface $customer){
        if ($this->request->getParam('is_subscribed', false)) {
            $subscribed = true;
        } else {
            $checkSubscriber = $this->subscriber->loadByEmail($customer->getEmail());
            $subscribed = $checkSubscriber->isSubscribed();
        }

        $created_at = new \DateTime($customer->getCreatedAt());
        $updated_at = new \DateTime($customer->getUpdatedAt());

        $groups = [];
        if(!empty($customer->getGroupId())){
            $group = $this->customerGroupRepository->getById($customer->getGroupId());
            $groups[] = [
                'id' => $group->getId(),
                'name' => $group->getCode(),
            ];
        }
        $gender = null;
        switch($customer->getGender()){
            case 1:
                $gender = 'male';
                break;
            case 2:
                $gender = 'female';
                break;
        }

        $address = $customer->getAddresses();
        $customerInfo = [
            'id' => (int)$customer->getId(),
            'email' => $customer->getEmail(),
            'accepts_marketing' => $subscribed,
            'title' => $customer->getPrefix(),
            'first_name' => $customer->getFirstname(),
            'last_name' => $customer->getLastname(),
            'created_at' => $created_at->format(\DateTime::ATOM ),
            'updated_at' => $updated_at->format(\DateTime::ATOM ),
            'guest' => false,
            'default_address' => empty($address) ? null : $this->addressSerializer->serialize($address[0]),
            'groups' => $groups,
            'gender' => $gender,
            'birthdate' => $customer->getDob()
        ];

        return $customerInfo;
    }
}
