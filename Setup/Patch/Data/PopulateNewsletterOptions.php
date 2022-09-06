<?php

namespace OuterEdge\Multiplenewsletter\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as NewsCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use OuterEdge\Multiplenewsletter\Helper\Data;
use Magento\Framework\App\State;
use Magento\Framework\App\ResourceConnection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

class PopulateNewsletterOptions implements DataPatchInterface
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
     * @var State
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

    /**
     * @inheritdoc
     */
    public function apply()
    {
        try {
            $this->state->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($e->getMessage() == 'Area code is not set') {
                $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
            }
        }

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
                throw new \Exception('Error on multiple newsletter with customer: '
                    .$customerNewsletter['customer_id'].' Reason: '.$e->getMessage());
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [AddNewsletterOptionsAttr::class,];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
