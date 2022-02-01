<?php

namespace OuterEdge\Multiplenewsletter\Block\Form;

use Magento\Framework\View\Element\Template;
use OuterEdge\Layout\Helper\Data as LayoutHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\SessionFactory;
use Magento\Newsletter\Model\Subscriber;

class Options extends Template
{
    protected $_newsletterOptions;

    /**
     * @var LayoutHelper
     */
    protected $layoutHelper;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var Subscriber
     */
    protected $subscriber;

    /**
     * @param Context $context
     * @param LayoutHelper $layoutHelper
     * @param SessionFactory $sessionFactory
     * @param Subscriber $subscriber
     * @param array $data
     */
    public function __construct(
        Context $context,
        LayoutHelper $layoutHelper,
        SessionFactory $sessionFactory,
        Subscriber $subscriber,
        array $data = []
    ) {
        $this->layoutHelper = $layoutHelper;
        $this->sessionFactory = $sessionFactory;
        $this->subscriber = $subscriber;
        parent::__construct($context, $data);
    }

    public function getNewsletterOptions()
    {
        if ($this->_newsletterOptions === null) {
            $this->_newsletterOptions = $this->layoutHelper->getGroupAndElements('newsletter_options');
        }
        return $this->_newsletterOptions;
    }

    public function getNewsletterOptionsByCustomer($newsletterOption)
    {
        $customerId = $this->getCustomerId();
        if (empty($customerId)) {
            return false;
        }

        $customerNews = $this->subscriber->loadByCustomerId($customerId);
        $customerNewsOpt = $customerNews->getNewsletterOptions();

        $newsOptions = [];
        if (!empty($customerNewsOpt)) {
            $result = json_decode($customerNewsOpt, true);

            if (isset($result[$newsletterOption])) {
                if ($result[$newsletterOption] == 1) {
                    return true;
                }
            }
        }
        return false;
    }

    private function getCustomerId()
    {
        return $this->sessionFactory->create()->getId();
    }

}
