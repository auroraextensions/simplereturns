<?php
/**
 * ResultsView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ViewModel\Orders
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Orders;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\{
    Helper\Config as ConfigHelper,
    Model\ValidatorModel\Sales\Order\EligibilityValidator,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};

use function count;

class ResultsView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @var EligibilityValidator $validator */
    protected $validator;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param EligibilityValidator $validator
     * @param array $data
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        EligibilityValidator $validator,
        array $data = []
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );
        $this->validator = $validator;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getReturnLabelUrl(OrderInterface $order): string
    {
        return $this->urlBuilder->getUrl(
            self::ROUTE_SIMPLERETURNS_LABEL_INDEX,
            [
                self::PARAM_ORDER_ID => $order->getRealOrderId(),
                self::PARAM_PROTECT_CODE => $order->getProtectCode(),
                '_secure' => true,
            ]
        );
    }

    /**
     * @return bool
     */
    public function hasOrders(): bool
    {
        /** @var array $orders */
        $orders = $this->getData('orders') ?? [];
        return count($orders) > 0;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isOrderEligible(OrderInterface $order): bool
    {
        return $this->validator->isOrderEligible($order);
    }
}
