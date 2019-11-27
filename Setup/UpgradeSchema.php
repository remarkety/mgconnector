<?php

namespace Remarkety\Mgconnector\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    private $connection;

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableName = $setup->getTable('mgconnector_queue');
        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $this->getConnection($setup);
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

        if (version_compare($context->getVersion(), '2.3.3', '<')) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $this->getConnection($setup);
                $connection->modifyColumn(
                    $tableName,
                    'payload',
                    ['type' => Table::TYPE_TEXT, 'length' => '2M', 'nullable' => false, 'comment' => 'Change to mediumtext']
                );
            }
        }


        $setup->endSetup();
    }

    private function getConnection($setup) {
        if (!$this->connection) {
            $this->connection = $setup->getConnection();
        }

        return $this->connection;
    }
}
