<?php

namespace OuterEdge\Multiplenewsletter\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use OuterEdge\Multiplenewsletter\Helper\Data;

class UpdateFromAdmin implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customerId = $observer->getCustomer()->getId();
        $subscriptionStatus = (array)$observer->getRequest()->getParam('subscription_status');

        if (empty($subscriptionStatus)) {
            return;
        }

        $customer = $this->customerRepositoryInterface->getById((int)$customerId);

        foreach ($subscriptionStatus as $status) {
            if ($status) {
                $customer->setCustomAttribute('newsletter_options', Data::CORE_NEWSLETTER_SUBSCRIBE);
            } else {
                $customer->setCustomAttribute('newsletter_options', Data::CORE_NEWSLETTER_UNSUBSCRIBE);
            }

            try {
                $this->customerRepositoryInterface->save($customer);
            } catch (\Exception $e) {
                throw new \Exception('Error saving multiple newsletter');
            }
        }
    }
}
