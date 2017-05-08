<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Install\Grid\Render;

/**
 * Store render website
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Website extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var $websiteRepo \Magento\Store\Model\WebsiteRepository
         */
        $websiteRepo = $objectManager->get('Magento\Store\Model\WebsiteRepository');
        $website = $websiteRepo->getById($row->getWebsiteId());

        return $website->getName();
    }
}
