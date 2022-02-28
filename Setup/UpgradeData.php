<?php

namespace OuterEdge\Multiplenewsletter\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory as NewsCollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use OuterEdge\Multiplenewsletter\Helper\Data;
use Magento\Framework\App\State;

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
     * @param NewsCollectionFactory $subscriberCollection
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param State $state
     */
    public function __construct(
        NewsCollectionFactory $subscriberCollection,
        CustomerRepositoryInterface $customerRepositoryInterface,
        State $state
    ) {
        $this->subscriberCollection = $subscriberCollection;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->state = $state;
    }

    public function getSubscriberCollection()
    {
        return $this->subscriberCollection->create();
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            $setup->startSetup();

            foreach ($this->getSubscriberCollection() as $customerNewsletter) {

                if (!$customerNewsletter['customer_id']) {
                    continue;
                }
                try {
                    $customer = $this->customerRepositoryInterface->getById($customerNewsletter['customer_id']);

                    //Only update if newsletter_options is empty
                    if ($customer->getCustomAttribute('newsletter_options')) {
                        continue;
                    }

                    //TODO. Save in a different way, or force to save even if customer have errors
                    $customer->setCustomAttribute('newsletter_options', Data::CORE_NEWSLETTER);
                    $this->customerRepositoryInterface->save($customer);

                } catch (\Exception $e) {
                    throw new \Exception('Error updating multiple newsletter, customer: ' . $customerNewsletter['customer_id']. ' Reason: '. $e->getMessage());
                }

            }
            $setup->endSetup();
		}
    }
}
