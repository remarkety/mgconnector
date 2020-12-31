<?php

namespace Remarkety\Mgconnector\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('mgconnector_queue'))
            ->addColumn('queue_id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'Remarkety queue ID')
            ->addColumn('event_type', Table::TYPE_TEXT, 100, ['nullable' => false], 'Event_type')
            ->addColumn('payload', Table::TYPE_TEXT, null, ['nullable' => false], 'Payload')
            ->addColumn('attempts', Table::TYPE_SMALLINT, null, ['nullable' => false], 'Attempts')
            ->addColumn('last_attempt', Table::TYPE_DATETIME, null, ['nullable' => true], 'Last_attempt')
            ->addColumn('next_attempt', Table::TYPE_DATETIME, null, ['nullable' => true], 'Next_attempt')
            ->addColumn('status', Table::TYPE_SMALLINT, '1', ['nullable' => true], 'Status')
            ->setComment('Remarkety queue table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
