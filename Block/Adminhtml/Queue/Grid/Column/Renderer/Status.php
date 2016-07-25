<?php

use Magento\Framework\DataObject;

/**
 * Adminhtml queue grid status column renderer block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Queue\Grid\Column\Renderer; class Status
    extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Column renderer
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        return Mage::helper('mgconnector')->__($value ? 'Queued' : 'Failed');
    }
}