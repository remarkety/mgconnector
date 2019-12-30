<?php
namespace Remarkety\Mgconnector\Helper;

use Magento\Framework\Module\Manager;
use Magento\Framework\ObjectManagerInterface;

class RewardPointsFactory
{
    protected $moduleManager;
    protected $objectManager;

    public function __construct(
        Manager $moduleManager,
        ObjectManagerInterface $objectManager
    )
    {
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    public function create(array $data = array())
    {
        if ($this->moduleManager->isEnabled('Aheadworks_RewardPoints')) {
            $instanceName = 'Aheadworks\RewardPoints\Api\CustomerRewardPointsManagementInterface';
            return $this->objectManager->create($instanceName, $data);
        } else {
            return null;
        }
    }
}