<?php


namespace Remarkety\Mgconnector\Serializer;

trait CheckSubscriberTrait
{
    private function cleanEmail($email)
    {
        $email = empty($email) ? '' : $email;
        return trim(strtolower($email));
    }

    private function checkSubscriber($email, $customer_id)
    {
        if (!empty($email)) {
            $newsletter = $this->subscriber->loadByEmail($email);
            if ($this->cleanEmail($email) == $this->cleanEmail($newsletter->getEmail())) {
                return $newsletter->isSubscribed();
            }
        }
        if (!empty($customer_id)) {
            $newsletter = $this->subscriber->loadByCustomerId($customer_id);
            if ($newsletter->getCustomerId() == $customer_id) {
                return $newsletter->isSubscribed();
            }
        }
        return false;
    }
}
