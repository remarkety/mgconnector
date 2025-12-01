<?php
/**
 * Observer to capture product prices before catalog rule indexing
 * Triggered by: indexer_catalogrule_apply_all_before event
 */

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Remarkety\Mgconnector\Service\PriceChangeDetector;
use Psr\Log\LoggerInterface;

class BeforeIndexObserver implements ObserverInterface
{
    /**
     * @var PriceChangeDetector
     */
    protected $priceChangeDetector;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        PriceChangeDetector $priceChangeDetector,
        LoggerInterface $logger
    ) {
        $this->priceChangeDetector = $priceChangeDetector;
        $this->logger = $logger;
    }

    /**
     * Execute observer - capture prices before indexing
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $this->logger->info('Remarkety: Starting price snapshot before catalog rule indexing');
            $this->priceChangeDetector->captureOldPrices();
        } catch (\Exception $e) {
            $this->logger->error('Remarkety: Error capturing price snapshot: ' . $e->getMessage());
        }
    }
}
