<?php
/**
 * PackageManagement.php
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
    Api\PackageManagementInterface,
    Api\Data\LabelInterface,
    Api\Data\LabelInterfaceFactory,
    Api\Data\PackageInterface,
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Model\AdapterModel\Shipping\Carrier\CarrierFactory,
    Model\SystemModel\Module\Config as ModuleConfig,
    Shared\ModuleComponentInterface
};
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Sales\{
    Api\Data\OrderInterface,
    Api\OrderRepositoryInterface
};

class PackageManagement implements PackageManagementInterface, ModuleComponentInterface
{
    /** @property CarrierFactory $carrierFactory */
    protected $carrierFactory;

    /** @property DirectoryHelper $directoryHelper */
    protected $directoryHelper;

    /** @property LabelInterfaceFactory $labelFactory */
    protected $labelFactory;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /**
     * @param CarrierFactory $carrierFactory
     * @param DirectoryHelper $directoryHelper
     * @param LabelInterfaceFactory $labelFactory
     * @param ModuleConfig $moduleConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @return void
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        DirectoryHelper $directoryHelper,
        LabelInterfaceFactory $labelFactory,
        ModuleConfig $moduleConfig,
        OrderRepositoryInterface $orderRepository,
        SimpleReturnRepositoryInterface $simpleReturnRepository
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->directoryHelper = $directoryHelper;
        $this->labelFactory = $labelFactory;
        $this->moduleConfig = $moduleConfig;
        $this->orderRepository = $orderRepository;
        $this->simpleReturnRepository = $simpleReturnRepository;
    }

    /**
     * Create return shipment package(s).
     *
     * @param PackageInterface $package
     * @return array
     */
    public function createShipmentPackages(PackageInterface $package): array
    {
        /** @var array $packages */
        $packages = [];

        /** @var int $rmaId */
        $rmaId = (int) $package->getRmaId();

        try {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->simpleReturnRepository->getById($rmaId);

            /** @var OrderInterface $order */
            $order = $this->orderRepository->get($rma->getOrderId());

            /** @var int|string $storeId */
            $storeId = $order->getStoreId();

            /** @var string $carrierCode */
            $carrierCode = $this->moduleConfig->getShippingCarrier($storeId);

            /** @var CarrierInterface $carrierModel */
            $carrierModel = $this->carrierFactory->create($carrierCode);

            /** @var float $packageWeight */
            $packageWeight = $this->moduleConfig->getPackageWeight($storeId);

            /** @var array $items */
            $items = [];

            /** @var array $visibleItems */
            $visibleItems = $order->getAllVisibleItems();

            foreach ($visibleItems as $item) {
                $items[] = $item->toArray();
            }

            /** @var string $description */
            $description = __(
                self::FORMAT_RMA_ORDER_REFERENCE,
                $order->getRealOrderId()
            )->__toString();

            /** @var array $containers */
            $containers = $carrierModel->getContainerTypesAll();

            /** @var string $container */
            $container = $this->getContainerCode(
                $carrierCode,
                (int) $storeId
            );

            /** @var array $params */
            $params = [
                'container'       => $containers[$container],
                'description'     => $description,
                'dimension_units' => $this->getDimensionUnit(),
                'weight_units'    => $this->getWeightUnit(),
                'weight'          => $packageWeight,
            ];

            $packages[] = [
                'params' => $params,
                'items'  => $items,
            ];
        } catch (NoSuchEntityException $e) {
            /* No action required. */
        } catch (LocalizedException $e) {
            /* No action required. */
        }

        return $packages;
    }

    /**
     * @param string $code
     * @param int $store
     * @return string
     */
    public function getContainerCode(string $code, int $store): string
    {
        return $this->moduleConfig->getContainerCode(
            $code,
            $store
        );
    }

    /**
     * @return string
     * @todo: Finish implementing.
     */
    public function getDimensionUnit(): string
    {
        return \Zend_Measure_Length::INCH;
    }

    /**
     * @return string
     */
    public function getWeightUnit(): string
    {
        return $this->directoryHelper->getWeightUnit();
    }
}
