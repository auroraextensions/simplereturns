<?php
/**
 * CreateView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ViewModel\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Rma;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Helper\Action as ActionHelper,
    Helper\Config as ConfigHelper,
    Model\AdapterModel\Sales\Order as OrderAdapter,
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
use Magento\Sales\Api\Data\OrderInterface;

use function __;
use function array_shift;

class CreateView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @var MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @var ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @var OrderInterface $order */
    protected $order;

    /** @var OrderAdapter $orderAdapter */
    protected $orderAdapter;

    /** @var string $route */
    private $route;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param MessageManagerInterface $messageManager
     * @param ModuleConfig $moduleConfig
     * @param OrderAdapter $orderAdapter
     * @param array $data
     * @param string $route
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        MessageManagerInterface $messageManager,
        ModuleConfig $moduleConfig,
        OrderAdapter $orderAdapter,
        array $data = [],
        string $route = self::ROUTE_SIMPLERETURNS_RMA_CREATEPOST
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
        $this->orderAdapter = $orderAdapter;
        $this->route = $route;
    }

    /**
     * @return OrderInterface|null
     * @throws NoSuchEntityException
     */
    public function getOrder(): ?OrderInterface
    {
        if ($this->order !== null) {
            return $this->order;
        }

        /** @var int|string $orderId */
        $orderId = $this->request->getParam(self::PARAM_ORDER_ID);

        /** @var string $orderId */
        $protectCode = $this->request->getParam(self::PARAM_PROTECT_CODE);

        if ($orderId !== null && $protectCode !== null) {
            /** @var array $fields */
            $fields = [
                self::FIELD_INCREMENT_ID => $orderId,
                self::FIELD_PROTECT_CODE => $protectCode,
            ];

            try {
                /** @var OrderInterface[] $orders */
                $orders = $this->orderAdapter->getOrdersByFields($fields);

                if (!empty($orders)) {
                    $this->order = array_shift($orders);
                    return $this->order;
                }

                /** @var NoSuchEntityException $exception */
                $exception = $this->exceptionFactory->create(
                    NoSuchEntityException::class,
                    __('Unable to locate any matching orders.')
                );
                throw $exception;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getViewOrderUrl(): ?string
    {
        /** @var OrderInterface $order */
        $order = $this->getOrder();

        if ($order !== null) {
            return $this->urlBuilder->getUrl(
                'sales/order/view',
                [
                    'order_id' => $order->getId(),
                    '_secure' => true,
                ]
            );
        }

        return null;
    }

    /**
     * @return array
     */
    public function getReasons(): array
    {
        return $this->moduleConfig->getReasons();
    }

    /**
     * @return array
     */
    public function getResolutions(): array
    {
        return $this->moduleConfig->getResolutions();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPostActionUrl(
        string $route = '',
        array $params = []
    ): string {
        /** @var int|string|null $orderId */
        $orderId = $this->request->getParam(self::PARAM_ORDER_ID);

        if ($orderId !== null) {
            $params['order_id'] = $orderId;
        }

        /** @var string|null $protectCode */
        $protectCode = $this->request->getParam(self::PARAM_PROTECT_CODE);

        if ($protectCode !== null) {
            $params['code'] = $protectCode;
        }

        return parent::getPostActionUrl($this->route, $params);
    }
}
