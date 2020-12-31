<?php

/**
 * Adminhtml queue grid status column renderer block
 *
 * @category   Remarkety
 * @package    Remarkety_Mgconnector
 * @author     Piotr Pierzak <piotrek.pierzak@gmail.com>
 */
namespace Remarkety\Mgconnector\Block\Adminhtml\Queue\Grid\Column\Renderer;

use Magento\Framework\DataObject;

class EventType extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    private $serialize;
    public function __construct(\Magento\Framework\Serialize\Serializer\Serialize $serialize)
    {
        $this->serialize = $serialize;
        parent::__construct();
    }

    /**
     * Column renderer
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        try {
            $data = $this->serialize->unserialize($row->getData('payload'));
            $payload = json_encode($data);
        } catch (\Exception $e) {
            $payload = "?";
        }
        return '<span title="'.htmlentities($payload).'">'.$value.'</span>';
    }
}
