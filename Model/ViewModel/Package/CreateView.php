<?php
/**
 * CreateView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Package;

use AuroraExtensions\SimpleReturns\{
    Exception\ExceptionFactory,
    Helper\Action as ActionHelper,
    Helper\Config as ConfigHelper,
    Model\SystemModel\Module\Config as ModuleConfig,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    Exception\NoSuchEntityException,
    Message\ManagerInterface as MessageManagerInterface,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};

class CreateView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @property MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param MessageManagerInterface $messageManager
     * @param ModuleConfig $moduleConfig
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = [],
        MessageManagerInterface $messageManager,
        ModuleConfig $moduleConfig
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );

        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
    }
}
