<?php
/**
 * AbstractTransport.php
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
namespace AuroraExtensions\SimpleReturns\Model\Email;

use Magento\Backend\App\ConfigInterface;
use Magento\Framework\{
    App\Config\ScopeConfigInterface,
    Mail\Template\TransportBuilder
};

abstract class AbstractTransport
{
    /** @property ConfigInterface $config */
    protected $config;

    /** @property array $data */
    protected $data;

    /** @property ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /** @property TransportBuilder $transportBuilder */
    protected $transportBuilder;

    /**
     * @param ConfigInterface $config
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param array $data
     */
    public function __construct(
        ConfigInterface $config,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        array $data = []
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->data = $data;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get data value by key.
     *
     * @return array
     */
    public function getData($key)
    {
        return $this->data[$key] ?: null;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * @return \Magento\Framework\Mail\Template\TransportBuilder
     */
    public function getTransportBuilder()
    {
        return $this->transportBuilder;
    }
}
