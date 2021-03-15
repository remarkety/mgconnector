<?php
declare(strict_types=1);

namespace Remarkety\Mgconnector\Controller\Ajax;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Remarkety\Mgconnector\Serializer\QuoteSerializer;

class AddEvent implements HttpGetActionInterface
{
    /**
     * @var QuoteSerializer
     */
    private $quoteSerializer;

    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @param QuoteSerializer $quoteSerializer
     * @param JsonFactory $jsonFactory
     */
    public function __construct(QuoteSerializer $quoteSerializer, JsonFactory $jsonFactory)
    {
        $this->quoteSerializer = $quoteSerializer;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        return $this->jsonFactory
            ->create()
            ->setData($this->quoteSerializer->serialize());
    }
}
