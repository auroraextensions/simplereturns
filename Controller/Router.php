<?php
/**
 * Router.php
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
namespace AuroraExtensions\SimpleReturns\Controller;

use AuroraExtensions\SimpleReturns\{
    Helper\Action as ActionHelper,
    Helper\Config as ConfigHelper,
    Shared\ModuleComponentInterface
};

use Magento\{
    Framework\App\ActionFactory,
    Framework\App\RequestInterface,
    Framework\App\ResponseInterface,
    Framework\App\RouterInterface,
    Store\Model\StoreManagerInterface
};

class Router implements RouterInterface, ModuleComponentInterface
{
    /** @property ActionFactory $actionFactory */
    protected $actionFactory;

    /** @property ConfigHelper $configHelper */
    protected $configHelper;

    /** @property ResponseInterface $response */
    protected $response;

    /** @property StoreManagerInterface $storeManager */
    protected $storeManager;

    /**
     * @param ActionFactory $actionFactory
     * @param ConfigHelper $configHelper
     * @param ResponseInterface $response
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        ActionFactory $actionFactory,
        ConfigHelper $configHelper,
        ResponseInterface $response,
        StoreManagerInterface $storeManager
    ) {
        $this->actionFactory = $actionFactory;
        $this->configHelper = $configHelper;
        $this->response = $response;
        $this->storeManager = $storeManager;
    }

    /**
     * Match returns_label_<ACTION> to controller.
     *
     * @param RequestInterface $request
     * @return Magento\Framework\App\ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        /** @var string $routePath */
        $routePath = ActionHelper::getUrlNoScript($request->getRequestUri(), $request->getBaseUrl());
        $routePath = strtolower($routePath);

        /** @var string $fullAction */
        $fullAction = ActionHelper::getFullActionFromRoutePath($routePath);

        /** @var array $actionKeys */
        $actionKeys = array_keys(self::DICT_ACTION_CONTROLLER_DISPATCH);

        if (!in_array($fullAction, $actionKeys)) {
            return null;
        }

        /** @var int $storeId */
        $storeId = $this->storeManager->getStore()->getId();

        if (!$this->configHelper->isModuleEnabled($storeId)) {
            return $this->actionFactory->create(NoRoute::class);
        }

        /** @var array $parts */
        $parts = explode(self::FULLACTION_DELIMITER, $fullAction);

        if ($fullAction === self::FULLACTION_RETURNS_LABEL_INDEX) {
            /** @var string $basePath */
            $basePath = implode('/', $parts);

            /** @var array $actionParams */
            $actionParams = ActionHelper::parseActionParams(
                ActionHelper::getUrlNoRouteControllerAction($routePath, $basePath)
            ) ?? [];

            $request->setParams($actionParams);
        }

        /**
         * @var string $routeName
         * @var string $controllerName
         * @var string $actionName
         */
        [
            $routeName,
            $controllerName,
            $actionName,
        ] = $parts;

        $request->setRouteName($routeName)
            ->setControllerName($controllerName)
            ->setActionName($actionName)
            ->setDispatched(true);

        return $this->actionFactory->create(self::DICT_ACTION_CONTROLLER_DISPATCH[$fullAction], ['request' => $request]);
    }
}
