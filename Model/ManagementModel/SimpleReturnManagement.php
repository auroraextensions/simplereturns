<?php
/**
 * SimpleReturnManagement.php
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

namespace AuroraExtensions\SimpleReturns\Model\ManagementModel;

use AuroraExtensions\SimpleReturns\{
    Api\SimpleReturnManagementInterface,
    Api\Data\PackageInterface,
    Api\Data\PackageInterfaceFactory,
    Api\Data\SimpleReturnInterface,
    Helper\Config as ConfigHelper,
    Model\AdapterModel\Carrier\CarrierFactory,
    Shared\ModuleComponentInterface
};
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Ups\Model\Carrier as UpsCarrier;
use Zend_Measure_Length as LengthUnits;
use Zend_Measure_Weight as WeightUnits;

class SimpleReturnManagement implements SimpleReturnManagementInterface, ModuleComponentInterface
{
    /** @property CarrierFactory $carrierFactory */
    protected $carrierFactory;

    /** @property ConfigHelper $configHelper */
    protected $configHelper;

    /** @property PackageInterfaceFactory $packageFactory */
    protected $packageFactory;

    /** @property RemoteAddress $remoteAddress */
    protected $remoteAddress;

    /**
     * @param CarrierFactory $carrierFactory
     * @param ConfigHelper $configHelper
     * @param PackageInterfaceFactory $packageFactory
     * @param RemoteAddress $remoteAddress
     * @return void
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        ConfigHelper $configHelper,
        PackageInterfaceFactory $packageFactory,
        RemoteAddress $remoteAddress
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->configHelper = $configHelper;
        $this->packageFactory = $packageFactory;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Add status update comment to return.
     *
     * @param string $comment
     * @return bool
     * @todo: Implement this method.
     */
    public function addComment(string $comment): bool
    {
        return true;
    }

    /**
     * Create package for return shipment.
     *
     * @param SimpleReturnInterface $rma
     * @return PackageInterface
     */
    public function createPackage(SimpleReturnInterface $rma): PackageInterface
    {
        /** @var array $items */
        $items = [];

        /** @var OrderInterface $order */
        $order = $rma->getOrder();

        /** @var array $visibleItems */
        $visibleItems = $order->getAllVisibleItems();

        foreach ($visibleItems as $item) {
            $items[] = $item->toArray();
        }

        /** @var int|string $storeId */
        $storeId = $order->getStoreId();

        /** @var string $carrierCode */
        $carrierCode = $this->configHelper->getShippingCarrier($storeId);

        /** @var CarrierInterface $carrierModel */
        $carrierModel = $this->carrierFactory->create($carrierCode);

        /** @var array $containers */
        $containers = $carrierModel->getContainerTypesAll();

        /** @var string $container */
        $container = $this->configHelper->getContainer($storeId);

        /** @var array $data */
        $data = [
            'container'       => $containers[$container],
            'description'     => $this->getRmaOrderReference($order),
            'dimension_units' => LengthUnits::INCH,
            'weight_units'    => WeightUnits::POUND,
            'weight'          => $order->getWeight(),
            'items'           => $items,
        ];

        /** @var PackageInterface $pacakge */
        $package = $this->packageFactory->create();
        $package->addData($data);

        return $package;
    }

    /**
     * Create return shipment.
     *
     * @param SimpleReturnInterface $rma
     * @return bool
     */
    public function createShipment(OrderInterface $order): bool
    {
    }

    /**
     * Get RMA order reference for return label.
     *
     * @param OrderInterface $order
     * @return string
     */
    public function getRmaOrderReference(OrderInterface $order): string
    {
        return __(
            self::FORMAT_RMA_ORDER_REFERENCE,
            $order->getIncrementId()
        );
    }

    /**
     * Get RMA request comment for noting on order.
     *
     * @param string $trackingNumber
     * @return string
     */
    public function getRmaRequestComment(string $trackingNumber): string
    {
        return __(
            self::FORMAT_RMA_REQUEST_COMMENT,
            $this->remoteAddress->getRemoteAddress(),
            $trackingNumber
        );
    }
}
