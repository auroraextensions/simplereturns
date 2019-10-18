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
    Component\System\ModuleConfigTrait,
    Exception\ExceptionFactory,
    Helper\Config as ConfigHelper,
    Model\ViewModel\AbstractView,
    Shared\Component\LabelFormatterTrait,
    Shared\ModuleComponentInterface,
    Spec\System\Module\ConfigInterface
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
    use ModuleConfigTrait, LabelFormatterTrait;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param ConfigInterface $moduleConfig
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = [],
        ConfigInterface $moduleConfig
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
}
