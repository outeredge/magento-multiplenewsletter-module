<?php

namespace OuterEdge\Multiplenewsletter\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as NewsCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use OuterEdge\Multiplenewsletter\Helper\Data;
use Magento\Framework\App\State;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var NewsCollectionFactory
     */
    protected $subscriberCollection;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @param NewsCollectionFactory $subscriberCollection
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param State $state
     * @param ResourceConnection $resource
     * @param Attribute $eavAttribute
     */
    public function __construct(
        NewsCollectionFactory $subscriberCollection,
        CustomerRepositoryInterface $customerRepositoryInterface,
        State $state,
        ResourceConnection $resource,
        Attribute $eavAttribute
    ) {
        $this->subscriberCollection = $subscriberCollection;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->state = $state;
        $this->resource = $resource;
        $this->eavAttribute = $eavAttribute;
    }

    public function getSubscriberCollection()
    {
        return $this->subscriberCollection->create();
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $setup->startSetup();
            $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            $customerEntity = $connection->getTableName('customer_entity');
            $customerEntityVarchar = $connection->getTableName('customer_entity_varchar');
            $attributeId = $this->eavAttribute->getIdByCode(\Magento\Customer\Model\Customer::ENTITY, 'newsletter_options');

            foreach ($this->getSubscriberCollection() as $customerNewsletter) {

                if (!$customerNewsletter['customer_id']) {
                    continue;
                }

                $selectCustomer = "SELECT entity_id FROM $customerEntity WHERE entity_id LIKE ".$customerNewsletter['customer_id'];
                $customerExist = $connection->fetchRow($selectCustomer);
                if (!$customerExist) {
                    continue;
                }

                try {
                    $customer = $this->customerRepositoryInterface->getById($customerNewsletter['customer_id']);
                    //Only update if newsletter_options is empty
                    if ($customer->getCustomAttribute('newsletter_options')) {
                        continue;
                    }

                    $connection->fetchAll("INSERT INTO `".$customerEntityVarchar."`
                        (`value_id`, `attribute_id`, `entity_id`, `value`)
                        VALUES (NULL, '".$attributeId."', '".$customer->getId()."', '".Data::CORE_NEWSLETTER_SUBSCRIBE."')");

                } catch (\Exception $e) {
                    throw new \Exception('Error updating multiple newsletter, customer: ' . $customerNewsletter['customer_id']. ' Reason: '. $e->getMessage());
                }
            }
            $setup->endSetup();
		}
    }
}
