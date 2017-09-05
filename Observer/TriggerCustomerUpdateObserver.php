<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\CustomerFactory;
use \Magento\Framework\Registry;
use \Magento\Newsletter\Model\Subscriber;
use \Magento\Customer\Model\Group;
use Remarkety\Mgconnector\Helper\ConfigHelper;
use \Remarkety\Mgconnector\Model\Queue;
use \Magento\Store\Model\Store;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Remarkety\Mgconnector\Serializer\AddressSerializer;
use Remarkety\Mgconnector\Serializer\CustomerSerializer;
use Remarkety\Mgconnector\Serializer\OrderSerializer;


class TriggerCustomerUpdateObserver extends EventMethods implements ObserverInterface
{
    /**
     * Apply catalog price rules to product in admin
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if($this->request->getFullActionName() == "customer_account_loginPost"){
                return $this;
            }
            /**
             * @var $backendModel \Magento\Customer\Model\Backend\Customer
             */
            $backendModel = $observer->getEvent()->getData('customer');
            /**
             * @var $customer \Magento\Customer\Api\Data\CustomerInterface
             */
            $customer = $backendModel->getDataModel();
            $customerOld = $this->customerRepository->getById($customer->getId());

            if($this->_coreRegistry->registry('remarkety_customer_save_observer_executed_'.$customer->getId()) || !$customer->getId()) {
                return $this;
            }
            $this->_coreRegistry->register('remarkety_customer_save_observer_executed_'.$customer->getId(),true);

            $isNew = $customer->getCreatedAt() == $customer->getUpdatedAt();

            if($customerOld && $customerOld->getStoreId() !== $customer->getStoreId()){
                //customer moved to a new store, send delete event to previous store
                $oldStore = $this->storeManager->getStore($customerOld->getStoreId());
                if($this->isWebhooksEnabled($oldStore)) {
                    $this->makeRequest(self::EVENT_CUSTOMERS_DELETED, array(
                        'id' => (int)$customer->getId(),
                        'email' => $customer->getEmail(),
                    ), $oldStore->getId());
                }
            }

            $this->_customerUpdate($customer, $isNew);
        } catch (\Exception $ex){
            $this->logError($ex);
        }
        return $this;
    }
}
