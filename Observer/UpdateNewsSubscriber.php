<?php

namespace OuterEdge\Multiplenewsletter\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use OuterEdge\Multiplenewsletter\Helper\Data;

class UpdateNewsSubscriber implements ObserverInterface
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
    public function execute(Observer $observer) : void
    {
        $event = $observer->getEvent();

        if ($customerId = $event->getSubscriber()->getCustomerId()) {
            if ($event->getSubscriber()) {
                $dataToSave = Data::CORE_NEWSLETTER_SUBSCRIBE;
            } else {
                $dataToSave = Data::CORE_NEWSLETTER_UNSUBSCRIBE;
            }

            try {
                $customer = $this->customerRepositoryInterface->getById($customerId);
                $customer->setCustomAttribute('newsletter_options', $dataToSave);

                $this->customerRepositoryInterface->save($customer);
            } catch (\Exception $e) {
                throw new \Exception('Error saving multiple newsletter');
            }
        }
    }
}
