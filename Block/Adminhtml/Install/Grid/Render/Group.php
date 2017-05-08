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
class Group extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * {@inheritdoc}
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /**
         * @var $groupRepository \Magento\Store\Model\GroupRepository
         */
        $groupRepository = $objectManager->get('Magento\Store\Model\GroupRepository');
        $group = $groupRepository->get($row->getGroupId());

        return $group->getName();
    }
}
