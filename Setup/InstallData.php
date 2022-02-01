<?php

namespace OuterEdge\Multiplenewsletter\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallData implements InstallDataInterface
{
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable('newsletter_subscriber'),
            'newsletter_options',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => '',
                'comment' => 'Newsletter Options'
            ]
        );
	}
}
