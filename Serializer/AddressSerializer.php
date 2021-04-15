<?php

namespace Remarkety\Mgconnector\Serializer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Data\Address;
use Magento\Directory\Model\CountryFactory;

class AddressSerializer
{
    private $countryFactory;

    /**
     * @param CountryFactory $countryFactory
     */
    public function __construct(
        CountryFactory $countryFactory
    ) {
        $this->countryFactory = $countryFactory;
    }

    /**
     * @param AddressInterface|Address $address
     *
     * @return array|null
     */
    public function serialize($address)
    {
        if (is_null($address)) {
            return null;
        }
        $countryCode = $address->getCountryId();
        $countryName = null;
        if (!empty($countryCode)) {
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            if (!empty($country)) {
                $countryName = $country->getName();
            }
        }
        $region = null;
        $regionStr = null;
        if ($address instanceof \Magento\Sales\Model\Order\Address) {
            $region = $address->getRegionCode();
        } else {
            $region = $address->getRegion();
        }
        if (!empty($region)) {
            if (is_object($region)) {
                $regionStr = $region->getRegionCode();
            } else {
                $regionStr = $region;
            }
        }
        $data = [
            'first_name' => $address->getFirstname(),
            'last_name' => $address->getLastname(),
            'city' => $address->getCity(),
            'street' => implode(PHP_EOL, $address->getStreet()),
            'country_code' => $countryCode,
            'country' => $countryName,
            'zip' => $address->getPostcode(),
            'phone' => $address->getTelephone(),
            'region' => $regionStr
        ];
        return $data;
    }
}
