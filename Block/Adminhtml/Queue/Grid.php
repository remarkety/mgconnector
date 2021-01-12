<?php

/**
 * Adminhtml queue grid block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Queue;

use \Magento\Backend\Block\Template\Context;
use \Magento\Backend\Helper\Data;
//use \Magento\Framework\Module\Manager;
use \Remarkety\Mgconnector\Model\QueueFactory;
use \Magento\Catalog\Model\ProductFactory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Prepare block
     */
    /**
     * @var \Magento\Framework\Module\Manager
     */
//    protected $moduleManager;
    protected $_productFactory;
    protected $_queueFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */

    public function __construct(Context $context, Data $backendHelper, QueueFactory $queueFactory, ProductFactory $productFactory, array $data = [])
    {
//        $this->moduleManager = $moduleManager;
        $this->_queueFactory = $queueFactory;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct() {
        parent::_construct();

        $this->setId('queue_ids');
        $this->setDefaultSort('queue_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setVarNameFilter('grid_record');
    }
    protected function _prepareCollection()
    {
        $collection = $this->_queueFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
//        return $collection;
//        $this->setCollection($collection);
//
//        $this->getCollection();
//        return $this;
//        parent::_prepareCollection();
//
//        return $this;
//        var_dump('1');
//        $collection = $this->_collectionFactory->create();
//        var_dump('2');
//        $this->setCollection($collection);
//        return $this;
//        return parent::_prepareCollection();

    }

    /**
     * Prepare columns
     *
     * @return Remarkety_Mgconnector_Block_Adminhtml_Queue_Grid
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('queue_id', array(
            'header' => __('Queue #'),
            'width' => '50px',
            'type' => 'number',
            'index' => 'queue_id'
        ));
        $this->addColumn('event_type', array(
            'header' => __('Event Type'),
            'index' => 'event_type',
        ));
        $this->addColumn('status', array(
            'header' => __('Status'),
            'width' => '200px',
            'index' => 'status'
        ));
        $this->addColumn('attempts', array(
            'header' => __('Attempts'),
            'index' => 'attempts'
        ));
        $this->addColumn('last_attempt', array(
            'header' => __('Last Attempt'),
            'width' => '200px',
            'type' => 'datetime',
            'index' => 'last_attempt'
        ));
        $this->addColumn('next_attempt', array(
            'header' => __('Next Attempt'),
            'width' => '200px',
            'type' => 'datetime',
            'index' => 'next_attempt'
        ));

        $this->addColumn('last_error_message', array(
            'header' => __('Last error'),
            'width' => '200px',
            'index' => 'last_error_message'
        ));

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('queue_id');
        $this->getMassactionBlock()->setFormFieldName('queue');

        $this->getMassactionBlock()->addItem(
            'resend',
            [
                'label' => __('Resend'),
                'url' => $this->getUrl('/*/resend')
            ]
        );

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('/*/delete'),
                'confirm' => __('Are you sure?')
            ]
        );


        return $this;
    }
}
