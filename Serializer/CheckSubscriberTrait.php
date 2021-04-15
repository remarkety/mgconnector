<?php

namespace Remarkety\Mgconnector\Serializer;

trait CheckSubscriberTrait
{
    private function cleanEmail($email)
    {
        return trim(strtolower($email));
    }

    /**
     * @param string $email
     * @param int|null $customerId
     * @param int $websiteId
     *
     * @return bool
     */
    private function checkSubscriber(string $email, $customerId, int $websiteId): bool
    {
        $subscriber = $this->subscriberFactory->create();

        if (!empty($email)) {
            $newsletter = $subscriber->loadBySubscriberEmail($email, $websiteId);
            if ($this->cleanEmail($email) == $this->cleanEmail($newsletter->getEmail())) {
                return $newsletter->isSubscribed();
            }
        }
        if (!empty($customerId)) {
            $newsletter = $subscriber->loadByCustomer($customerId, $websiteId);
            if ($newsletter->getCustomerId() == $customerId) {
                return $newsletter->isSubscribed();
            }
        }
        return false;
    }
}
