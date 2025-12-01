<?php
/**
 * Observer to sync product price changes after catalog rule indexing
 * Triggered by: indexer_catalogrule_apply_all_after event
 */

namespace Remarkety\Mgconnector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Remarkety\Mgconnector\Service\PriceChangeDetector;
use Psr\Log\LoggerInterface;

class AfterIndexObserver implements ObserverInterface
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
     * Execute observer - compare prices and sync changes
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $this->logger->info('Remarkety: Starting price change detection after catalog rule indexing');
            $this->priceChangeDetector->syncOnlyChanges();
        } catch (\Exception $e) {
            $this->logger->error('Remarkety: Error syncing price changes: ' . $e->getMessage());
        }
    }
}
