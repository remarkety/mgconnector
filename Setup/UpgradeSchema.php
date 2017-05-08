<?php

namespace Remarkety\Mgconnector\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableName = $setup->getTable('mgconnector_queue');
        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $connection->addColumn(
                    $tableName,
                    'store_id',
                    ['type' => Table::TYPE_INTEGER,'nullable' => false, 'default' => 0, 'afters' => 'queue_id', 'comment' => 'Magento store id']
                );
                $connection->addColumn(
                    $tableName,
                    'last_error_message',
                    ['type' => Table::TYPE_TEXT,'nullable' => false, 'default' => '', 'comment' => 'last error message']
                );
            }
        }


        $setup->endSetup();

    }
}
