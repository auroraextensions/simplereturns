<?php
/**
 * Generator.php
 *
 * Return label generation service model.
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
namespace AuroraExtensions\SimpleReturns\Model\ServiceModel\Label;

use AuroraExtensions\SimpleReturns\{
    Api\PackageManagementInterface,
    Helper\Config as ConfigHelper,
    Model\CarrierFactory,
    Shared\ModuleComponentInterface
};

use Magento\{
    Framework\HTTP\PhpEnvironment\RemoteAddress,
    Sales\Api\Data\OrderInterface,
    Shipping\Model\Carrier\CarrierInterface,
    Ups\Model\Carrier as UpsCarrier
};

class Generator implements PackageManagementInterface, ModuleComponentInterface
{
    /** @property CarrierFactory $carrierFactory */
    protected $carrierFactory;

    /** @property ConfigHelper $configHelper */
    protected $configHelper;

    /** @property RemoteAddress $remoteAddress */
    protected $remoteAddress;

    /**
     * @param CarrierFactory $carrierFactory
     * @param ConfigHelper $configHelper
     * @param RemoteAddress $remoteAddress
     * @return void
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        ConfigHelper $configHelper,
        RemoteAddress $remoteAddress
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->configHelper = $configHelper;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * Create package(s) for return shipment.
     *
     * @param OrderInterface $order
     * @return array
     */
    public function createShipmentPackages(OrderInterface $order): array
    {
        /** @var array $packages */
        $packages = [];

        /** @var array $items */
        $items = [];

        /** @var int|string $originStoreId */
        $originStoreId = $order->getStoreId();

        /** @var string $carrierCode */
        $carrierCode = $this->configHelper->getShippingCarrier($originStoreId);

        /** @var array $visibleItems */
        $visibleItems = $order->getAllVisibleItems();

        foreach ($visibleItems as $item) {
            switch ($carrierCode) {
                case UpsCarrier::CODE:
                    $items[] = $item->toArray();
                default:
                    break;
            }
        }

        /** @var array $package */
        $package = [
            'params' => [
                'weight' => $this->configHelper->getPackageWeight($originStoreId),
            ],
            'items' => $items,
        ];

        switch ($carrierCode) {
            case UpsCarrier::CODE:
                /** @var CarrierInterface $carrierModel */
                $carrierModel = $this->getCarrierModel($carrierCode);

                /** @var array $params */
                $params = $package['params'];

                /** @var array $containers */
                $containers = $carrierModel->getContainerTypesAll();

                /** @var string $container */
                $container = $this->configHelper->getUpsContainer($originStoreId);

                /** @var array $fields */
                $fields = [
                    'container'       => $containers[$container] ?? self::DEFAULT_UPS_SHIPPING_CONTAINER_TYPE,
                    'weight_units'    => \Zend_Measure_Weight::POUND,
                    'dimension_units' => \Zend_Measure_Length::INCH,
                    'description'     => $this->getRmaOrderReference($order),
                ];

                $package['params'] = array_merge($params, $fields);
                $packages[] = $package;
            default:
                break;
        }

        return $packages;
    }

    /**
     * Get carrier model by carrier code.
     *
     * @param string $code
     * @return CarrierInterface
     */
    protected function getCarrierModel(string $code = 'ups'): CarrierInterface
    {
        return $this->carrierFactory->create($code);
    }

    /**
     * Get RMA order reference for return label.
     *
     * @param OrderInterface $order
     * @return string
     */
    public function getRmaOrderReference(OrderInterface $order): string
    {
        return __(self::FORMAT_RMA_ORDER_REFERENCE, $order->getIncrementId());
    }

    /**
     * Get RMA request comment for noting on order.
     *
     * @param string $trackingNumber
     * @return string
     */
    public function getRmaRequestComment(string $trackingNumber): string
    {
        return __(self::FORMAT_RMA_REQUEST_COMMENT, $this->remoteAddress->getRemoteAddress(), $trackingNumber);
    }
}
