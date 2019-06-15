<?php
/** 
 * ListView.php
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

namespace AuroraExtensions\SimpleReturns\Model\ViewModel;

use AuroraExtensions\SimpleReturns\{
    Exception\ExceptionFactory,
    Helper\Action as ActionHelper,
    Helper\Config as ConfigHelper,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};
use Magento\Sales\Api\Data\OrderInterface;

class ListView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );
    }

    /**
     * Get return label URL.
     *
     * @param OrderInterface $order
     * @return string
     */
    public function getReturnLabelUrl(OrderInterface $order): string
    {
        return $this->urlBuilder->getUrl(
            self::ROUTE_RETURNS_LABEL_INDEX,
            [
                self::PARAM_ORDER_ID => $order->getRealOrderId(),
                self::PARAM_PROTECT_CODE => $order->getProtectCode(),
                '_secure' => true,
            ]
        );
    }

    /**
     * Check if customer has existing orders.
     *
     * @return bool
     */
    public function hasOrders(): bool
    {
        /** @var array $orders */
        $orders = $this->getData('orders') ?? [];

        return (bool)(count($orders) > 0);
    }

    /**
     * Check if order is eligible for prepaid return labels.
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function isOrderPrepaidEligible(OrderInterface $order): bool
    {
        return true;
    }
}
