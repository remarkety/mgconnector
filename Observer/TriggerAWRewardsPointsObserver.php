<?php

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\CustomerFactory;

class TriggerAWRewardsPointsObserver extends EventMethods implements ObserverInterface
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
            if (!$this->configHelper->isAheadworksRewardPointsEnabled()) {
                //integration was disabled
                return $this;
            }
            /**
             * @var \Aheadworks\RewardPoints\Api\Data\TransactionInterface $transaction
             */
            $transaction = $observer->getEvent()->getData('entity');
            $current_balance = $transaction->getCurrentBalance();
            $aw_transaction_id = $transaction->getTransactionId();
            if (is_null($current_balance)) {
                $this->_coreRegistry->register('aw_transaction_id', $aw_transaction_id);
                //no need to update yet
                return $this;
            }
            $aw_transaction_id_registered = $this->_coreRegistry->registry('aw_transaction_id');
            if ($aw_transaction_id_registered !== $aw_transaction_id) {
                //this is not a relevant transaction
                return $this;
            }
            $customer_id = $transaction->getCustomerId();
            if (!$customer_id) {
                return $this;
            }
            $customer_email = $transaction->getCustomerEmail();
            $customer = $this->customerRepository->getById($customer_id);
            if ($customer) {
                if ($this->isWebhooksEnabled($customer->getStoreId())) {
                    $eventType = self::EVENT_CUSTOMERS_UPDATED;
                    $data = [
                        'id' => $customer->getId(),
                        'email' => $customer_email,
                        'rewards' => [
                            'points' => $current_balance
                        ]
                    ];
                    $this->makeRequest($eventType, $data, $customer->getStoreId());
                }
            }
        } catch (\Exception $ex) {
            $this->logError($ex);
        }
        return $this;
    }
}
