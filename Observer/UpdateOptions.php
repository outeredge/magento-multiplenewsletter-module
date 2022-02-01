<?php

namespace OuterEdge\Multiplenewsletter\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\Subscriber;

class UpdateOptions implements ObserverInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

     /**
     * @var Subscriber
     */
    protected $subscriber;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Subscriber $subscriber
     * @param SessionFactory $sessionFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Subscriber $subscriber,
        SessionFactory $sessionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->subscriber = $subscriber;
        $this->sessionFactory = $sessionFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer) : void
    {
        $newsOptions = $observer->getRequest()->getParam('newsletter_options', false);
        if ($newsOptions) {

            $dataToSave = json_encode($newsOptions);

            $storeId = (int)$this->storeManager->getStore()->getId();
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();

            try {
                $subscriber = $this->subscriber->loadByCustomerId($this->getCustomerId(), $websiteId);
                $subscriber->setNewsletterOptions($dataToSave)->save();
            } catch (\Exception $e) {
                throw new \Exception('Error saving multiple newsletter');
            }
        }
    }

    private function getCustomerId()
    {
        return $this->sessionFactory->create()->getId();
    }
}
