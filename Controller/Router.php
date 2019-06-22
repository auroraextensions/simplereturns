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
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller;

use AuroraExtensions\SimpleReturns\{
    Helper\Action as ActionHelper,
    Helper\Config as ConfigHelper,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\ActionFactory,
    App\RequestInterface,
    App\ResponseInterface,
    App\RouterInterface,
    DataObject\Factory as DataObjectFactory
};
use Magento\Store\Model\StoreManagerInterface;

class Router implements RouterInterface, ModuleComponentInterface
{
    /** @property ActionFactory $actionFactory */
    protected $actionFactory;

    /** @property ConfigHelper $configHelper */
    protected $configHelper;

    /** @property DataObjectFactory $dataObjectFactory */
    protected $dataObjectFactory;

    /** @property ResponseInterface $response */
    protected $response;

    /** @property StoreManagerInterface $storeManager */
    protected $storeManager;

    /** @property DataObject $settings */
    protected $settings;

    /**
     * @param ActionFactory $actionFactory
     * @param ConfigHelper $configHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param ResponseInterface $response
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        ActionFactory $actionFactory,
        ConfigHelper $configHelper,
        DataObjectFactory $dataObjectFactory,
        ResponseInterface $response,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->actionFactory = $actionFactory;
        $this->configHelper = $configHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->response = $response;
        $this->storeManager = $storeManager;
        $this->settings = $this->dataObjectFactory->create($data);
    }

    /**
     * Get dispatch array.
     *
     * @return array
     */
    protected function getDispatch(): array
    {
        return array_change_key_case(
            $this->settings->getData('dispatch') ?? []
        );
    }

    /**
     * Get full actions.
     *
     * @return array
     */
    protected function getFullActions(): array
    {
        return array_keys($this->getDispatch());
    }

    /**
     * Check if full action is valid for either
     * a specific partner or all partners.
     *
     * @param string $action
     * @param array $actions
     * @return bool
     */
    protected function isValid(
        string $action,
        array $actions = []
    ): bool
    {
        /* Default to checking all full actions. */
        $actions = !empty($actions) ? $actions : $this->getFullActions();

        return in_array($action, $actions);
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
        $routePath = strtolower(
            ActionHelper::getUrlNoScript($request->getRequestUri(), $request->getBaseUrl())
        );

        /** @var string $fullAction */
        $fullAction = ActionHelper::getFullActionFromRoutePath($routePath);

        /** @var int $storeId */
        $storeId = $this->storeManager->getStore()->getId();

        /** @var bool $isEnabled */
        $isEnabled = $this->configHelper->isModuleEnabled($storeId);

        if (!$this->isValid($fullAction) || !$isEnabled) {
            return $this->actionFactory->create(NoRouteHandler::class);
        }

        /** @var array $parts */
        $parts = explode(self::FULLACTION_DELIMITER, $fullAction);

        if ($fullAction === self::FULLACTION_SIMPLERETURNS_LABEL_INDEX) {
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

        /** @var string $actionClass */
        $actionClass = get_class(
            $this->getDispatch()[$fullAction]
        );

        return $this->actionFactory->create(
            $actionClass,
            [
                'request' => $request,
            ]
        );
    }
}
