<?php

namespace OuterEdge\Multiplenewsletter\Block\Form;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use OuterEdge\Multiplenewsletter\Helper\Data;

class Options extends Template
{

    protected $newsletterOptions;

    /**
     * @var SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param Context $context
     * @param SessionFactory $sessionFactory
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        SessionFactory $sessionFactory,
        CustomerRepositoryInterface $customerRepositoryInterface,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->sessionFactory = $sessionFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getNewsletterOptions()
    {
        if ($this->newsletterOptions === null) {
            $data = $this->scopeConfig->getValue('multinewletteroptions/list/options', ScopeInterface::SCOPE_STORE);
            foreach (explode(',', $data) as $option) {
                $this->newsletterOptions[] = [
                    'lable' => ucfirst(ltrim($option)),
                    'code' => str_replace(' ', '_', ltrim($option))
                ];
            }
        }

        return $this->newsletterOptions;
    }

    public function getNewsletterOptionsByCustomer($newsletterOption)
    {
        $customerId = $this->getCustomerId();
        if (empty($customerId)) {
            return false;
        }

        $customer = $this->customerRepositoryInterface->getById($customerId);

        if ($customer->getCustomAttribute('newsletter_options')) {
            $customerNewsOpt = $customer->getCustomAttribute('newsletter_options')->getValue();
        }

        if (!empty($customerNewsOpt)) {
            if ($customerNewsOpt == Data::CORE_NEWSLETTER_SUBSCRIBE) {
                return true;
            }

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
