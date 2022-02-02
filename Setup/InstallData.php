<?php

namespace OuterEdge\Multiplenewsletter\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config;
use Magento\Customer\Model\Customer;
use Magento\Customer\Api\CustomerMetadataInterface;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory, Config $eavConfig)
	{
		$this->eavSetupFactory = $eavSetupFactory;
		$this->eavConfig       = $eavConfig;
	}
	
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		$eavSetup->addAttribute(
			\Magento\Customer\Model\Customer::ENTITY,
			'newsletter_options',
			[
				'type'         => 'varchar',
				'label'        => 'Multi Newsletter',
				'input'        => 'text',
				'required'     => false,
				'visible'      => false,
				'user_defined' => true,
				'position'     => 999,
				'system'       => false,
			]
		);
		$eavSetup->addAttributeToSet(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
            null,
            'newsletter_options');

		$multiNews = $this->eavConfig->getAttribute(Customer::ENTITY, 'newsletter_options');
		$multiNews->setData(
			'used_in_forms',
			['adminhtml_customer']

		);
		$multiNews->save();

	}
}
