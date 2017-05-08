<?php
/**
 * Created by PhpStorm.
 * User: bnaya
 * Date: 4/26/17
 * Time: 2:54 PM
 */

namespace Remarkety\Mgconnector\Serializer;


class AddressSerializer
{
    private $_countryFactory;

    public function __construct(
        \Magento\Directory\Model\CountryFactory $countryFactory
    )
    {
        $this->_countryFactory = $countryFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order\Address|\Magento\Customer\Api\Data\AddressInterface $address
     * @return array
     */
    public function serialize($address){
        $country = $this->_countryFactory->create()->loadByCode($address->getCountryId());
        $region = $address->getRegion();
        $data = [
            'first_name' => $address->getFirstname(),
            'last_name' => $address->getLastname(),
            'city' => $address->getCity(),
            'street' => implode(PHP_EOL, $address->getStreet()),
            'country_code' => $address->getCountryId(),
            'country' => $country->getName(),
            'zip' => $address->getPostcode(),
            'phone' => $address->getTelephone(),
            'region' => !empty($region) ? $region->getRegionCode() : null
        ];
        return $data;
    }
}
