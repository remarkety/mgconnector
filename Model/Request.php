<?php

/**
 * Request model
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Model;

use Magento\Framework\UrlInterface;
use \Magento\Store\Model\Store;
use \Magento\Customer\Model\Session;

class Request
{
    const REMARKETY_URI = 'https://app.remarkety.com/public/install/notify';
    const REMARKETY_METHOD = 'POST';
    const REMARKETY_TIMEOUT = 10;
    const REMARKETY_VERSION = 0.9;
    const REMARKETY_PLATFORM = 'MAGENTO_2';


    public function __construct(
        Store $store,
        Session $customerSession
    ) {
        $this->store = $store;
        $this->session = $customerSession;
    }

    protected function _getRequestConfig()
    {
        return [
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
            'curloptions' => [
//                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HEADER => true,
                CURLOPT_CONNECTTIMEOUT => self::REMARKETY_TIMEOUT,
//                CURLOPT_SSL_CIPHER_LIST => "RC4-SHA"
//                CURLOPT_SSL_VERIFYPEER => false,
            ],
        ];
    }

    protected function _getPayloadBase()
    {
        $domain = $this->store->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        $domain = substr($domain, 7, -1);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion(); //will return the magento version

        $arr = [
            'domain' => $domain,
            'platform' => \Remarkety\Mgconnector\Model\Request::REMARKETY_PLATFORM,
            'version' => $version,
        ];

        return $arr;
    }

    public function makeRequest($payload)
    {
        try {
            $payload = array_merge($payload, $this->_getPayloadBase());

            $client = new \Zend_Http_Client(
                self::REMARKETY_URI,
                $this->_getRequestConfig()
            );

            $client->setParameterPost($payload);

            $response = $client->request(self::REMARKETY_METHOD);

            $body = (array)json_decode($response->getBody());
            $this->session->setRemarketyLastResponseStatus($response->getStatus() === 200 ? 1 : 0);
            $this->session->setRemarketyLastResponseMessage(serialize($body));


            switch ($response->getStatus()) {
                case '200':
                    return $body;
                case '400':
                    throw new \Exception('Request failed. ' . $body['message']);
                default:
                    throw new \Exception('Request to remarkety servers failed ('.$response->getStatus().')');
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
