<?php

/**
 * Adminhtml queue grid block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Install;

use \Magento\Store\Model\ResourceModel\Store\Collection as StoreCollection;
use \Remarkety\Mgconnector\Model\QueueFactory;
use \Magento\Catalog\Model\ProductFactory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        parent::__construct($context, $backendHelper, $data);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
    }

    public function getRowUrl($item)
    {
        return '';
    }

    protected function _prepareCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Magento\Store\Model\ResourceModel\Store\Collection $collection */
        $collection = $objectManager->create('Magento\Store\Model\ResourceModel\Store\Collection');

        $collection->addOrder('website_id')->addOrder('group_id');

        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('website_id', [
            'header' => __('Website'),
            'index' => 'website_id',
            'renderer' => 'Remarkety\Mgconnector\Block\Adminhtml\Install\Grid\Render\Website'
        ]);

        $this->addColumn('group_id', [
            'header' => __('Store'),
            'index' => 'group_id',
            'renderer' => 'Remarkety\Mgconnector\Block\Adminhtml\Install\Grid\Render\Group'
        ]);

        $this->addColumn('name', array(
            'header' => __('Store view'),
            'index' => 'name'
        ));

        $this->addColumn('store_id', [
            'header' => __('Status'),
            'index' => 'store_id',
            'renderer' => 'Remarkety\Mgconnector\Block\Adminhtml\Install\Grid\Render\Status'
        ]);

        return parent::_prepareColumns();
    }
}
