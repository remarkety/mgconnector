<?php
namespace Remarkety\Mgconnector\Model\Api\Data;
class StoreSettingsContact implements \Remarkety\Mgconnector\Api\Data\StoreSettingsContactInterface
{

    public $name;
    public $phone;
    public $email;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }
}