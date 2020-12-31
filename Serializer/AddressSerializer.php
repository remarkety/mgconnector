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
    ) {
        $this->_countryFactory = $countryFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order\Address|\Magento\Customer\Api\Data\AddressInterface $address
     * @return array
     */
    public function serialize($address)
    {
        if (is_null($address)) {
            return null;
        }
        $countryCode = $address->getCountryId();
        $countryName = null;
        if (!empty($countryCode)) {
            $country = $this->_countryFactory->create()->loadByCode($countryCode);
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
