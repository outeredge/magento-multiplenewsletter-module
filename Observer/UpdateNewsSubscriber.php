<?php

namespace OuterEdge\Multiplenewsletter\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use OuterEdge\Multiplenewsletter\Helper\Data;

class UpdateNewsSubscriber implements ObserverInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param SessionFactory $sessionFactory
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepositoryInterface,
        SessionFactory $sessionFactory
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer) : void
    {
        $event = $observer->getEvent();
        if ($event->getSubscriber()) {
            $dataToSave = Data::CORE_NEWSLETTER_SUBSCRIBE;
        } else {
            $dataToSave = Data::CORE_NEWSLETTER_UNSUBSCRIBE;
        }

        try {
            $customer = $this->customerRepositoryInterface->getById($this->getCustomerId());
            $customer->setCustomAttribute('newsletter_options', $dataToSave);

            $this->customerRepositoryInterface->save($customer);

        } catch (\Exception $e) {
            throw new \Exception('Error saving multiple newsletter');
        }
    }

    private function getCustomerId()
    {
        return $this->sessionFactory->create()->getId();
    }
}
