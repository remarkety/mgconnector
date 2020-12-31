<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\CustomerFactory;

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
            $this->startTiming(self::class);
            if ($this->ignoreCustomerUpdate()) {
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
            $this->_coreRegistry->register('remarkety_customer_id', $customer->getId(), true);

            if ($this->_coreRegistry->registry('remarkety_customer_save_observer_executed_'.$customer->getId()) || !$customer->getId()) {
                return $this;
            }
            $this->_coreRegistry->register('remarkety_customer_save_observer_executed_'.$customer->getId(), true);

            $isNew = $customer->getCreatedAt() == $customer->getUpdatedAt();

            if ($customerOld && $customerOld->getStoreId() != $customer->getStoreId()) {
                //customer moved to a new store, send delete event to previous store
                $oldStore = $this->storeManager->getStore($customerOld->getStoreId());
                if ($this->isWebhooksEnabled($oldStore)) {
                    $this->makeRequest(self::EVENT_CUSTOMERS_DELETED, [
                        'id' => (int)$customer->getId(),
                        'email' => $customer->getEmail(),
                    ], $oldStore->getId());
                }
            }

            $this->_customerUpdate($customer, $isNew);
            $this->endTiming(self::class);
        } catch (\Exception $ex) {
            $this->logError($ex);
        }
        return $this;
    }
}
