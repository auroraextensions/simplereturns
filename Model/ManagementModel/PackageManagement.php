<?php
/**
 * PackageManagement.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ManagementModel;

use Exception;
use AuroraExtensions\SimpleReturns\{
    Api\PackageManagementInterface,
    Api\PackageRepositoryInterface,
    Api\Data\LabelInterface,
    Api\Data\LabelInterfaceFactory,
    Api\Data\PackageInterface,
    Api\Data\SimpleReturnInterface,
    Api\LabelRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Component\Psr\Log\LoggerTrait,
    Exception\ExceptionFactory,
    Model\Security\Token as Tokenizer,
    Model\AdapterModel\Shipping\Carrier\CarrierFactory,
    Model\SystemModel\Module\Config as ModuleConfig,
    Shared\ModuleComponentInterface
};
use Magento\Directory\{
    Helper\Data as DirectoryHelper,
    Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory
};
use Magento\Framework\{
    DataObject,
    DataObject\Factory as DataObjectFactory,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    HTTP\PhpEnvironment\RemoteAddress,
    Message\ManagerInterface as MessageManagerInterface
};
use Magento\Sales\{
    Api\Data\OrderInterface,
    Api\OrderRepositoryInterface
};
use Magento\Shipping\{
    Model\Shipment\RequestFactory as ShipmentRequestFactory,
    Model\Shipment\ReturnShipment
};
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class PackageManagement implements PackageManagementInterface, ModuleComponentInterface
{
    use LoggerTrait;

    /** @property CarrierFactory $carrierFactory */
    protected $carrierFactory;

    /** @property DataObjectFactory $dataObjectFactory */
    protected $dataObjectFactory;

    /** @property DirectoryHelper $directoryHelper */
    protected $directoryHelper;

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property LabelInterfaceFactory $labelFactory */
    protected $labelFactory;

    /** @property LabelRepositoryInterface $labelRepository */
    protected $labelRepository;

    /** @property MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property PackageRepositoryInterface $packageRepository */
    protected $packageRepository;

    /** @property RegionCollectionFactory $regionCollectionFactory */
    protected $regionCollectionFactory;

    /** @property RemoteAddress $remoteAddress */
    protected $remoteAddress;

    /** @property ShipmentRequestFactory $shipmentRequestFactory */
    protected $shipmentRequestFactory;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @property StoreManagerInterface $storeManager */
    protected $storeManager;

    /**
     * @param CarrierFactory $carrierFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param DirectoryHelper $directoryHelper
     * @param ExceptionFactory $exceptionFactory
     * @param LabelInterfaceFactory $labelFactory
     * @param LabelRepositoryInterface $labelRepository
     * @param LoggerInterface $logger
     * @param MessageManagerInterface $messageManager
     * @param ModuleConfig $moduleConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param PackageRepositoryInterface $packageRepository
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param RemoteAddress $remoteAddress
     * @param ShipmentRequestFactory $shipmentRequestFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        DataObjectFactory $dataObjectFactory,
        DirectoryHelper $directoryHelper,
        ExceptionFactory $exceptionFactory,
        LabelInterfaceFactory $labelFactory,
        LabelRepositoryInterface $labelRepository,
        LoggerInterface $logger,
        MessageManagerInterface $messageManager,
        ModuleConfig $moduleConfig,
        OrderRepositoryInterface $orderRepository,
        PackageRepositoryInterface $packageRepository,
        RegionCollectionFactory $regionCollectionFactory,
        RemoteAddress $remoteAddress,
        ShipmentRequestFactory $shipmentRequestFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->directoryHelper = $directoryHelper;
        $this->exceptionFactory = $exceptionFactory;
        $this->labelFactory = $labelFactory;
        $this->labelRepository = $labelRepository;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
        $this->orderRepository = $orderRepository;
        $this->packageRepository = $packageRepository;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->remoteAddress = $remoteAddress;
        $this->shipmentRequestFactory = $shipmentRequestFactory;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->storeManager = $storeManager;
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

            /** @var int $storeId */
            $storeId = (int) $order->getStoreId();

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
            $description = $this->getOrderComment($order);

            /** @var string $containerCode */
            $containerCode = $this->moduleConfig->getContainerCode(
                $carrierCode,
                $storeId
            );

            /** @var string $containerType */
            $containerType = $this->moduleConfig->getContainerType(
                $carrierCode,
                $containerCode,
                $storeId
            );

            /** @var array $params */
            $params = [
                'container' => $containerType,
                'description' => $description,
                'dimension_units' => $this->getDimensionUnit(),
                'weight_units' => $this->getWeightUnit(),
                'weight' => $packageWeight,
            ];

            $packages[] = [
                'params' => $params,
                'items' => $items,
            ];
        } catch (NoSuchEntityException $e) {
            $this->getLogger()->error($e->getMessage());
        } catch (LocalizedException $e) {
            $this->getLogger()->error($e->getMessage());
        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage());
        }

        return $packages;
    }

    /**
     * @param PackageInterface $package
     * @return bool
     */
    public function requestToReturnShipment(PackageInterface $package): bool
    {
        /** @var int $rmaId */
        $rmaId = (int) $package->getRmaId();

        try {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->simpleReturnRepository->getById($rmaId);

            /** @var OrderInterface $order */
            $order = $this->orderRepository->get($rma->getOrderId());

            /** @var int $storeId */
            $storeId = (int) $order->getStoreId();

            /** @var AddressInterface $shipperAddress */
            $shipperAddress = $order->getShippingAddress();

            /** @var DataObject $returnsAddress */
            $returnsAddress = $this->moduleConfig->getOriginInfo($storeId);

            /** @var Shipment $originShipment */
            $originShipment = $order->getShipmentsCollection()
                ->getFirstItem();

            /** @var string $carrierCode */
            $carrierCode = $this->moduleConfig->getShippingCarrier($storeId);

            /** @var CarrierInterface $carrierModel */
            $carrierModel = $this->carrierFactory->create($carrierCode);

            /** @var string $currencyCode */
            $currencyCode = $this->storeManager
                ->getStore()
                ->getCurrentCurrency()
                ->getCode();

            /** @var Region|null $shipperRegion */
            $shipperRegion = $this->regionCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('main_table.region_id', ['eq' => $shipperAddress->getRegionId()])
                ->getFirstItem();

            /** @var Region|null $returnsRegion */
            $returnsRegion = $this->regionCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('main_table.region_id', ['eq' => $returnsAddress->getRegionId()])
                ->getFirstItem();

            /** @var int|string $shipperRegionCode */
            $shipperRegionCode = $shipperRegion->getCode();

            /** @var int|string $returnsRegionCode */
            $returnsRegionCode = $returnsRegion->getCode();

            /** @var DataObject $returnsRecipient */
            $returnsRecipient = $this->moduleConfig->getRecipientData($storeId);

            /** @var DataObject $storeInfo */
            $storeInfo = $this->moduleConfig->getStoreInfo($storeId);

            /** @var array $conditions */
            $conditions = [
                !$returnsRecipient->getFullName(),
                !$returnsRecipient->getLastName(),
                !$returnsAddress->getCompany(),
                !$storeInfo->getPhone(),
                !$returnsAddress->getStreet(),
                !$returnsAddress->getCity(),
                !$shipperRegionCode,
                !$returnsAddress->getPostcode(),
                !$returnsAddress->getCountryId(),
            ];

            if (in_array(true, $conditions)) {
                $this->messageManager->addErrorMessage('Unable to generate shipping label(s).');

                return false;
            }

            try {
                /** @var string|null $companyName */
                $companyName = $shipperAddress->getCompany();
                $companyName = !empty($companyName)
                    ? $companyName
                    : $order->getCustomerName();

                /** @var ShipmentRequest $shipmentRequest */
                $shipmentRequest = $this->shipmentRequestFactory->create();

                /** @var array|string $shipperStreet */
                $shipperStreet = $shipperAddress->getStreet();
                $shipperStreet = is_array($shipperStreet)
                    ? implode(
                        self::ADDRESS_FIELD_DELIMITER,
                        $shipperStreet
                    ) : $shipperStreet;

                /** @var array|string $returnsStreet */
                $returnsStreet = $returnsAddress->getStreet();
                $returnsStreet = is_array($returnsStreet)
                    ? implode(
                        self::ADDRESS_FIELD_DELIMITER,
                        $returnsStreet
                    ) : $returnsStreet;

                /** @var array $shipmentDetails */
                $shipmentDetails = [
                    'order_shipment'                           => $originShipment,
                    'shipper_contact_person_name'              => $order->getCustomerName(),
                    'shipper_contact_person_first_name'        => $order->getCustomerFirstname(),
                    'shipper_contact_person_last_name'         => $order->getCustomerLastname(),
                    'shipper_contact_company_name'             => $companyName,
                    'shipper_contact_phone_number'             => $shipperAddress->getTelephone(),
                    'shipper_email'                            => $shipperAddress->getEmail(),
                    'shipper_address_street'                   => $shipperStreet,
                    'shipper_address_street_1'                 => $shipperStreet,
                    'shipper_address_street_2'                 => $shipperAddress->getStreet2(),
                    'shipper_address_city'                     => $shipperAddress->getCity(),
                    'shipper_address_state_or_province_code'   => $shipperRegionCode,
                    'shipper_address_postal_code'              => $shipperAddress->getPostcode(),
                    'shipper_address_country_code'             => $shipperAddress->getCountryId(),
                    'recipient_contact_person_name'            => $returnsRecipient->getFullName(),
                    'recipient_contact_person_first_name'      => $returnsRecipient->getFirstName(),
                    'recipient_contact_person_last_name'       => $returnsRecipient->getLastName(),
                    'recipient_contact_company_name'           => $returnsAddress->getCompany(),
                    'recipient_contact_phone_number'           => $storeInfo->getPhone(),
                    'recipient_email'                          => $returnsAddress->getEmail(),
                    'recipient_address_street'                 => $returnsStreet,
                    'recipient_address_street_1'               => $returnsStreet,
                    'recipient_address_street_2'               => $returnsAddress->getStreet2(),
                    'recipient_address_city'                   => $returnsAddress->getCity(),
                    'recipient_address_state_or_province_code' => $returnsRegionCode,
                    'recipient_address_region_code'            => $returnsRegionCode,
                    'recipient_address_postal_code'            => $returnsAddress->getPostcode(),
                    'recipient_address_country_code'           => $returnsAddress->getCountryId(),
                    'shipping_method'                          => $this->moduleConfig->getShippingMethod($storeId),
                    'package_weight'                           => $this->getPackageWeight($order, $storeId),
                    'packages'                                 => $this->createShipmentPackages($package),
                    'base_currency_code'                       => $currencyCode,
                    'store_id'                                 => $storeId,
                    'reference_data'                           => $this->getOrderComment($order),
                ];

                /** @var DataObject $carrierResponse */
                $carrierResponse = $carrierModel->returnOfShipment(
                    $shipmentRequest->addData($shipmentDetails)
                );

                if ($carrierResponse->getErrors()) {
                    /** @var LocalizedException $exception */
                    $exception = $this->exceptionFactory->create(
                        LocalizedException::class,
                        __($carrierResponse->getErrors())
                    );

                    throw $exception;
                }

                /** @var array $responseDetails */
                $responseDetails = $carrierResponse->getInfo();

                if (!empty($responseDetails)) {
                    /** @var array|null $shipmentInfo */
                    $shipmentInfo = $responseDetails[0] ?? null;

                    if ($shipmentInfo !== null) {
                        /** @var LabelInterface $label */
                        $label = $this->labelFactory->create();

                        /** @var string|null $labelImage */
                        $labelImage = $shipmentInfo['label_content'] ?? null;

                        /** @var string|null $trackingNumber */
                        $trackingNumber = $shipmentInfo['tracking_number'] ?? null;

                        /** @var string $token */
                        $token = Tokenizer::createToken();

                        /** @var int $labelId */
                        $labelId = $this->labelRepository->save(
                            $label->addData([
                                'pkg_id'          => $package->getId(),
                                'image'           => $labelImage,
                                'tracking_number' => $trackingNumber,
                                'remote_ip'       => $this->remoteAddress->getRemoteAddress(),
                                'token'           => $token,
                            ])
                        );
                        $package->setLabelId($labelId);
                        $this->packageRepository->save($package);

                        /* Add RMA comment to order. */
                        $order->addStatusHistoryComment(
                            $this->getRmaComment($trackingNumber)
                        );
                        $this->orderRepository->save($order);
                    }
                }

                return true;
            } catch (NoSuchEntityException $e) {
                throw $e;
            } catch (LocalizedException $e) {
                throw $e;
            } catch (Exception $e) {
                throw $e;
            }
        } catch (NoSuchEntityException $e) {
            /* No action required. */
        } catch (LocalizedException $e) {
            /* No action required. */
        } catch (Exception $e) {
            /* No action required. */
        }

        return false;
    }

    /**
     * @param OrderInterface $order
     * @param int $store
     * @return float
     */
    public function getPackageWeight(OrderInterface $order, int $store): float
    {
        /** @var float $weight */
        $weight = (float)($order->getWeight() ?? $this->moduleConfig->getPackageWeight($store));

        return $weight;
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getOrderComment(OrderInterface $order): string
    {
        return __(
            self::FORMAT_RMA_ORDER_COMMENT,
            $order->getRealOrderId()
        )->__toString();
    }

    /**
     * @param string $trackingNumber
     * @return string
     */
    public function getRmaComment(string $trackingNumber): string
    {
        return __(
            self::FORMAT_RMA_REQUEST_COMMENT,
            $this->remoteAddress->getRemoteAddress(),
            $trackingNumber
        )->__toString();
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
     * @todo: Finish implementing.
     */
    public function getWeightUnit(): string
    {
        return \Zend_Measure_Weight::POUND;
    }
}
