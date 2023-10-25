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
use \Laminas;

class Request
{
    const REMARKETY_URI = 'https://app.remarkety.com/public/install/notify';
    const REMARKETY_METHOD = Laminas\Http\Request::METHOD_POST;
    const REMARKETY_TIMEOUT = 10;
    const REMARKETY_VERSION = 0.9;
    const REMARKETY_PLATFORM = 'MAGENTO_2';

    private $store;
    private $session;
    private $serialize;
    public function __construct(
        Store $store,
        Session $customerSession,
        \Magento\Framework\Serialize\Serializer\Serialize $serialize
    ) {
        $this->store = $store;
        $this->session = $customerSession;
        $this->serialize = $serialize;
    }

    protected function _getHttpClientConfig()
    {
        return [
            'timeout' => self::REMARKETY_TIMEOUT,
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

            /**
             * Docs: https://docs.laminas.dev/laminas-http/client/intro/
             */
            $client = new Laminas\Http\Client();
            $client->setOptions($this->_getHttpClientConfig());

            /**
             * Docs: https://docs.laminas.dev/laminas-http/request/
             */
            $request = new Laminas\Http\Request();
            $request->setMethod(self::REMARKETY_METHOD);
            $request->setUri(self::REMARKETY_URI);
            foreach ($payload as $key => $value) {
                $request->getPost()->set($key, $value);
            }

            /**
             * Docs: https://docs.laminas.dev/laminas-http/response/
             */
            $response = $client->send($request);

            $body = (array)json_decode($response->getBody());
            $this->session->setRemarketyLastResponseStatus($response->getStatusCode() === 200 ? 1 : 0);
            $this->session->setRemarketyLastResponseMessage($this->serialize->serialize($body));

            switch ($response->getStatusCode()) {
                case '200':
                    return $body;
                case '400':
                    throw new \Exception('Request failed. ' . $body['message']);
                default:
                    throw new \Exception('Request to remarkety servers failed ('.$response->getStatusCode().')');
            }

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
