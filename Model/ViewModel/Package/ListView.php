<?php
/** 
 * ListView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */ 
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Package;

use AuroraExtensions\SimpleReturns\{
    Exception\ExceptionFactory,
    Helper\Config as ConfigHelper,
    Model\SystemModel\Module\Config as ModuleConfig,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};

class ListView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param ModuleConfig $moduleConfig
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = [],
        ModuleConfig $moduleConfig
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );

        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Get frontend label for field type by key.
     *
     * @param string $type
     * @param string $key
     * @param string
     */
    public function getFrontLabel(string $type, string $key): string
    {
        /** @var array $labels */
        $labels = $this->moduleConfig
            ->getSettings()
            ->getData($type);

        return $labels[$key] ?? $key;
    }
}
