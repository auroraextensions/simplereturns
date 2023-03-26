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
use AuroraExtensions\SimpleReturns\Helper\Config as ConfigHelper;
use AuroraExtensions\SimpleReturns\Model\Adapter\Sales\Order as OrderAdapter;
use AuroraExtensions\SimpleReturns\Model\SystemModel\Module\Config as ModuleConfig;
use AuroraExtensions\SimpleReturns\Model\ViewModel\AbstractView;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;

use function __;
use function array_shift;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateView extends AbstractView implements ArgumentInterface
{
    private const FIELD_INCREMENT_ID = 'increment_id';
    private const FIELD_PROTECT_CODE = 'protect_code';
    private const PARAM_ORDER_ID = 'order_id';
    private const PARAM_PROTECT_CODE = 'code';
    private const ROUTE_PATH = 'simplereturns/rma/createPost';

    /** @var MessageManagerInterface $messageManager */
    private $messageManager;

    /** @var ModuleConfig $moduleConfig */
    private $moduleConfig;

    /** @var OrderInterface $order */
    private $order;

    /** @var OrderAdapter $orderAdapter */
    private $orderAdapter;

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
        string $route = self::ROUTE_PATH
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

                if (empty($orders)) {
                    /** @var NoSuchEntityException $exception */
                    $exception = $this->exceptionFactory->create(
                        NoSuchEntityException::class,
                        __('Unable to locate any matching orders.')
                    );
                    throw $exception;
                }

                $this->order = array_shift($orders);
                return $this->order;
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
        /** @var string|null $targetUrl */
        $targetUrl = null;

        /** @var OrderInterface|null $order */
        $order = $this->getOrder();

        if ($order !== null) {
            $targetUrl = $this->urlBuilder->getUrl(
                'sales/order/view',
                [
                    'order_id' => $order->getId(),
                    '_secure' => true,
                ]
            );
        }

        return $targetUrl;
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
