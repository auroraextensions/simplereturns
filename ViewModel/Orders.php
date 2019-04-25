<?php
/** 
 * Orders.php
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
namespace AuroraExtensions\SimpleReturns\ViewModel;

use AuroraExtensions\SimpleReturns\{
    Helper\Action as ActionHelper,
    Model\Label\Processor,
    Shared\ModuleComponentInterface
};

use Magento\{
    Framework\App\RequestInterface,
    Framework\DataObject,
    Framework\UrlInterface,
    Framework\View\Element\Block\ArgumentInterface,
    Sales\Api\Data\OrderInterface
};

class Orders extends DataObject implements ArgumentInterface, ModuleComponentInterface
{
    /** @property Processor $processor */
    protected $processor;

    /** @property RequestInterface $request */
    protected $request;

    /** @property UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param Processor $processor
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @return void
     */
    public function __construct(
        Processor $processor,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($data);
        $this->processor = $processor;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
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
     * Get returns_label_ordersPost POST action URL.
     *
     * @return string
     */
    public function getPostActionUrl(): string
    {
        return $this->urlBuilder->getUrl(
            self::ROUTE_RETURNS_LABEL_ORDERSPOST,
            [
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
        return $this->processor->isOrderPrepaidEligible($order);
    }
}
