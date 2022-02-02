<?php

namespace OuterEdge\Multiplenewsletter\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class UpdateOptions implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param SessionFactory $sessionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepositoryInterface,
        SessionFactory $sessionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer) : void
    {
        $newsOptions = $observer->getRequest()->getParam('newsletter_options', false);

        $dataToSave = json_encode($newsOptions);

        $storeId = (int)$this->storeManager->getStore()->getId();
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();

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
