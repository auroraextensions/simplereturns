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
declare(strict_types=1);

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
use Magento\Store\{
    Model\ScopeInterface,
    Model\StoreManagerInterface
};

class Customer implements ModuleComponentInterface
{
    /** @property ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /** @property StoreManagerInterface $storeManager */
    protected $storeManager;

    /** @property TransportBuilder $transportBuilder */
    protected $transportBuilder;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @return void
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * @param string $template Template configuration ID.
     * @param string $identity Email sender identity XML path.
     * @param array $params
     * @param int $storeId
     * @return $this
     * @see Magento\Customer\Model\Customer::_sendEmailTemplate()
     */
    public function send(
        string $template,
        string $identity,
        array $params = [],
        string $email = null,
        string $name = null,
        int $storeId = null
    ) {
        /** @var int|string|null $storeId */
        $storeId = $storeId
            ?? $this->storeManager->getStore()->getId();

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

        /** @var string $sender */
        $sender = $this->scopeConfig->getValue(
            $identity,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        /** @var Magento\Framework\Mail\TransportInterface $transport */
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions($options)
            ->setTemplateVars($params)
            ->setFrom($sender)
            ->addTo($email, $name)
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }
}
