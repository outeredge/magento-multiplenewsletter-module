<?php

namespace OuterEdge\Multiplenewsletter\Block\Form;

use Magento\Framework\View\Element\Template;
use OuterEdge\Layout\Helper\Data as LayoutHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

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
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @param Context $context
     * @param LayoutHelper $layoutHelper
     * @param SessionFactory $sessionFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param array $data
     */
    public function __construct(
        Context $context,
        LayoutHelper $layoutHelper,
        SessionFactory $sessionFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        array $data = []
    ) {
        $this->layoutHelper = $layoutHelper;
        $this->sessionFactory = $sessionFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
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

        $customer = $this->customerRepositoryInterface->getById($customerId);
        $customerNewsOpt = $customer->getCustomAttribute('newsletter_options')->getValue();

        if (!empty($customerNewsOpt)) {
            if (strpos($customerNewsOpt, $newsletterOption) !== false) {
                return true;
            }
        }
        return false;
    }

    private function getCustomerId()
    {
        return $this->sessionFactory->create()->getId();
    }

}
