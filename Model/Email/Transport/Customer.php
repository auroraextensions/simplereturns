<?php
/**
 * Customer.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package        AuroraExtensions_SimpleReturns
 * @copyright      Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license        Aurora Extensions EULA
 */
namespace AuroraExtensions\SimpleReturns\Model\Email\Transport;

use AuroraExtensions\SimpleReturns\{
    Model\SystemModel\Config\Module as ModuleConfig,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\Area,
    App\Config\ScopeConfigInterface,
    Mail\Template\TransportBuilder
};
use Magento\Store\Model\ScopeInterface;

class Customer implements ModuleComponentInterface
{
    /** @property ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /** @property TransportBuilder $transportBuilder */
    protected $transportBuilder;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @return void
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * Send email notification to customer.
     *
     * @param Customer $customer
     * @param string $template Template configuration ID.
     * @param string $sender Email sender identity XML path.
     * @param array $variables
     * @param int|string|null $storeId
     * @return $this
     * @see Magento\Customer\Model\Customer::_sendEmailTemplate()
     */
    public function sendEmail(
        $customer,
        string $template,
        string $sender,
        array $variables = [],
        $storeId = null
    ) {
        /** @var int|string|null $storeId */
        $storeId = $storeId ?? $customer->getStoreId();

        /** @var string $templateId */
        $templateId = $this->scopeConfig->getValue(
            $template,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        /** @var array $options */
        $options = [
            'area'  => Area::AREA_FRONTEND,
            'store' => $storeId,
        ];

        /** @var string $identity */
        $identity = $this->scopeConfig->getValue(
            $sender,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        /** @var string $email */
        $email = $customer->getEmail();

        /** @var string $name */
        $name = $customer->getName();

        /** @var Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions($options)
            ->setTemplateVars($variables)
            ->setFrom($identity)
            ->addTo($email, $name)
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }
}
