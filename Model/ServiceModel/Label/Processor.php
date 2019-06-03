<?php
/**
 * Processor.php
 *
 * Return label processor service model.
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
    Api\LabelManagementInterface,
    Helper\Config as ConfigHelper,
    Model\CarrierFactory,
    Model\Label as LabelModel,
    Model\Orders as OrdersModel,
    Shared\ModuleComponentInterface
};

use Magento\{
    Catalog\Api\ProductRepositoryInterface,
    Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory,
    Framework\Escaper,
    Framework\Message\ManagerInterface as MessageManagerInterface,
    Framework\Pricing\PriceCurrencyInterface,
    Framework\Stdlib\DateTime,
    Rma\Helper\Data as ReturnHelper,
    Sales\Api\Data\OrderInterface,
    Sales\Api\OrderRepositoryInterface,
    Sales\Model\Service\ShipmentService,
    Shipping\Model\Carrier\CarrierInterface,
    Shipping\Model\Shipment\RequestFactory as ShipmentRequestFactory,
    Shipping\Model\Shipment\ReturnShipment,
    Store\Model\StoreManagerInterface
};

class Processor implements LabelManagementInterface, ModuleComponentInterface
{
    /** @property CarrierFactory $carrierFactory */
    protected $carrierFactory;

    /** @property ConfigHelper $configHelper */
    protected $configHelper;

    /** @property PriceCurrencyInterface $currency */
    protected $currency;

    /** @property Escaper $escaper */
    protected $escaper;

    /** @property Generator $generator */
    protected $generator;

    /** @property LabelModel $labelModel */
    protected $labelModel;

    /** @property MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @property OrderRepositoryInterface $orderRepository */
    protected $orderRepository;

    /** @property OrdersModel $ordersModel */
    protected $ordersModel;

    /** @property ProductRepositoryInterface $productRepository */
    protected $productRepository;

    /** @property RegionCollectionFactory $regionCollectionFactory */
    protected $regionCollectionFactory;

    /** @property ReturnHelper $returnHelper */
    protected $returnHelper;

    /** @property ShipmentRequestFactory $shipmentRequestFactory */
    protected $shipmentRequestFactory;

    /** @property ShipmentService $shipmentService */
    protected $shipmentService;

    /** @property StoreManagerInterface $storeManager */
    protected $storeManager;

    /**
     * @param CarrierFactory $carrierFactory
     * @param ConfigHelper $configHelper
     * @param PriceCurrencyInterface $currency
     * @param Escaper $escaper
     * @param Generator $generator
     * @param LabelModel $labelModel
     * @param MessageManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     * @param OrdersModel $ordersModel
     * @param ProductRepositoryInterface $productRepository
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param ReturnHelper $returnHelper
     * @param ShipmentRequestFactory $shipmentRequestFactory
     * @param ShipmentService $shipmentService
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        CarrierFactory $carrierFactory,
        ConfigHelper $configHelper,
        PriceCurrencyInterface $currency,
        Escaper $escaper,
        Generator $generator,
        LabelModel $labelModel,
        MessageManagerInterface $messageManager,
        OrderRepositoryInterface $orderRepository,
        OrdersModel $ordersModel,
        ProductRepositoryInterface $productRepository,
        RegionCollectionFactory $regionCollectionFactory,
        ReturnHelper $returnHelper,
        ShipmentRequestFactory $shipmentRequestFactory,
        ShipmentService $shipmentService,
        StoreManagerInterface $storeManager
    ) {
        $this->carrierFactory = $carrierFactory;
        $this->configHelper = $configHelper;
        $this->currency = $currency;
        $this->escaper = $escaper;
        $this->generator = $generator;
        $this->labelModel = $labelModel;
        $this->messageManager = $messageManager;
        $this->orderRepository = $orderRepository;
        $this->ordersModel = $ordersModel;
        $this->productRepository = $productRepository;
        $this->regionCollectionFactory = $regionCollectionFactory;
        $this->returnHelper = $returnHelper;
        $this->shipmentRequestFactory = $shipmentRequestFactory;
        $this->shipmentService = $shipmentService;
        $this->storeManager = $storeManager;
    }

    /**
     * Request prepaid return shipment label.
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function createShipmentLabel(OrderInterface $order): bool
    {
        if (!$this->isOrderPrepaidEligible($order, true)) {
            return false;
        }

        return $this->requestToReturnShipment($order);
    }

    /**
     * Check if order is eligible for prepaid return labels.
     *
     * @param OrderInterface $order
     * @param bool $outputErrors Append error messages for user.
     * @return bool
     */
    public function isOrderPrepaidEligible(
        OrderInterface $order,
        bool $outputErrors = false
    ): bool
    {
        if (!$this->isReturnLabelAllowedForItems($order)) {
            if ($outputErrors) {
                $this->messageManager->addError(
                    __(
                        self::ERROR_ORDER_HAS_INELIGIBLE_ITEMS,
                        $this->configHelper->getCustomerSupportEmail($order->getStoreId()),
                        $this->configHelper->getCustomerSupportName($order->getStoreId())
                    )
                );
            }

            return false;
        }

        if (!$this->isOrderAgeBelowThreshold($order)) {
            if ($outputErrors) {
                $this->messageManager->addError(
                    __(
                        self::ERROR_ORDER_EXCEEDS_AGE_THRESHOLD,
                        $this->configHelper->getOrderAgeMaximum($order->getStoreId())
                    )
                );
            }

            return false;
        }

        if (!$this->isOrderSubtotalAboveMinimum($order)) {
            if ($outputErrors) {
                $this->messageManager->addError(
                    __(
                        self::ERROR_ORDER_SUBTOTAL_BELOW_MINIMUM,
                        $this->currency->convertAndFormat(
                            $this->configHelper->getOrderAmountMinimum($order->getStoreId())
                        )
                    )
                );
            }

            return false;
        }

        return true;
    }

    /**
     * Request return shipment.
     *
     * @param OrderInterface $order
     * @return bool
     */
    protected function requestToReturnShipment(OrderInterface $order): bool
    {
        /** @var int $originStoreId */
        $originStoreId = $order->getStoreId();

        /** @var AddressInterface $shipperAddress */
        $shipperAddress = $order->getShippingAddress();
        $returnsAddress = $this->returnHelper->getReturnAddressModel($originStoreId);

        /** @var Shipment $originShipment */
        $originShipment = $this->getShipment($order);

        /** @var string $carrierCode */
        $carrierCode = $this->configHelper->getShippingCarrier($originStoreId);

        /** @var CarrierInterface $carrierModel */
        $carrierModel = $this->getCarrierModel($carrierCode);

        /** @var string $baseCurrencyCode Base currency code. */
        $baseCurrencyCode = $this->storeManager->getStore()->getCurrentCurrency()->getCode();

        /** @var Region|null $shipperRegion */
        $shipperRegion = $this->regionCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('main_table.region_id', ['eq' => $shipperAddress->getRegionId()])
            ->getFirstItem();

        $shipperRegionCode = $shipperRegion->getCode();
        $returnsRegionCode = $returnsAddress->getRegionId();

        /** @var string $returnsRecipient */
        $returnsRecipient = $this->configHelper->getRecipientData($originStoreId);

        /** @var DataObject $storeInfo */
        $storeInfo = $this->configHelper->getStoreInfo($originStoreId);

        /** @var array $conditions */
        $conditions = [
            !$returnsRecipient->getFullName(),
            !$returnsRecipient->getLastName(),
            !$returnsAddress->getCompany(),
            !$storeInfo->getPhone(),
            !$returnsAddress->getStreet(-1),
            !$returnsAddress->getCity(),
            !$shipperRegionCode,
            !$returnsAddress->getPostcode(),
            !$returnsAddress->getCountryId(),
        ];

        if (in_array(true, $conditions)) {
            $this->messageManager->addError('Insufficient information to create shipping label(s).');

            return false;
        }

        try {
            /** @var string|null $companyName */
            $companyName = $shipperAddress->getCompany();
            $companyName = !empty($companyName) ? $companyName : $order->getCustomerName();

            /** @var ShipmentRequest $shipmentRequest */
            $shipmentRequest = $this->shipmentRequestFactory->create();

            /** @var array|string $shipperStreet */
            $shipperStreet = $shipperAddress->getStreet();
            $shipperStreet = is_array($shipperStreet)
                ? implode(self::ADDRESS_FIELD_DELIMITER, $shipperStreet)
                : $shipperStreet;

            /** @var array|string $returnsStreet */
            $returnsStreet = $returnsAddress->getStreet();
            $returnsStreet = is_array($returnsStreet)
                ? implode(self::ADDRESS_FIELD_DELIMITER, $returnsStreet)
                : $returnsStreet;

            /** @var array $shipmentDetails */
            $shipmentDetails = [
                'order_shipment'                           => $originShipment,
                'shipper_contact_person_name'              => $this->escaper->escapeHtml($order->getCustomerName()),
                'shipper_contact_person_first_name'        => $this->escaper->escapeHtml($order->getCustomerFirstname()),
                'shipper_contact_person_last_name'         => $this->escaper->escapeHtml($order->getCustomerLastname()),
                'shipper_contact_company_name'             => $this->escaper->escapeHtml($companyName),
                'shipper_contact_phone_number'             => $this->escaper->escapeHtml($shipperAddress->getTelephone()),
                'shipper_email'                            => $this->escaper->escapeHtml($shipperAddress->getEmail()),
                'shipper_address_street'                   => $this->escaper->escapeHtml($shipperStreet),
                'shipper_address_street_1'                 => $this->escaper->escapeHtml($shipperStreet),
                'shipper_address_street_2'                 => $this->escaper->escapeHtml($shipperAddress->getStreet2()),
                'shipper_address_city'                     => $this->escaper->escapeHtml($shipperAddress->getCity()),
                'shipper_address_state_or_province_code'   => $this->escaper->escapeHtml($shipperRegionCode),
                'shipper_address_postal_code'              => $this->escaper->escapeHtml($shipperAddress->getPostcode()),
                'shipper_address_country_code'             => $this->escaper->escapeHtml($shipperAddress->getCountryId()),
                'recipient_contact_person_name'            => $this->escaper->escapeHtml($returnsRecipient->getFullName()),
                'recipient_contact_person_first_name'      => $this->escaper->escapeHtml($returnsRecipient->getFirstName()),
                'recipient_contact_person_last_name'       => $this->escaper->escapeHtml($returnsRecipient->getLastName()),
                'recipient_contact_company_name'           => $this->escaper->escapeHtml($returnsAddress->getCompany()),
                'recipient_contact_phone_number'           => $this->escaper->escapeHtml($storeInfo->getPhone()),
                'recipient_email'                          => $this->escaper->escapeHtml($returnsAddress->getEmail()),
                'recipient_address_street'                 => $this->escaper->escapeHtml($returnsStreet),
                'recipient_address_street_1'               => $this->escaper->escapeHtml($returnsStreet),
                'recipient_address_street_2'               => $this->escaper->escapeHtml($returnsAddress->getStreet2()),
                'recipient_address_city'                   => $this->escaper->escapeHtml($returnsAddress->getCity()),
                'recipient_address_state_or_province_code' => $this->escaper->escapeHtml($returnsRegionCode),
                'recipient_address_region_code'            => $this->escaper->escapeHtml($returnsRegionCode),
                'recipient_address_postal_code'            => $this->escaper->escapeHtml($returnsAddress->getPostcode()),
                'recipient_address_country_code'           => $this->escaper->escapeHtml($returnsAddress->getCountryId()),
                'shipping_method'                          => $this->configHelper->getShippingMethod($originStoreId),
                'package_weight'                           => $this->getPackageWeight($originShipment, $originStoreId),
                'packages'                                 => $this->generator->createShipmentPackages($order),
                'base_currency_code'                       => $baseCurrencyCode,
                'store_id'                                 => $originStoreId,
                'reference_data'                           => $this->generator->getRmaOrderReference($order),
            ];

            /* Add applicable fields to shipping request. */
            $shipmentRequest->setData($shipmentDetails);

            /** @var DataObject $requestResponse */
            $requestResponse = $carrierModel->returnOfShipment($shipmentRequest);

            if ($requestResponse->getErrors()) {
                throw new \Exception($requestResponse->getErrors());
            }

            /** @var array|object $responseDetails */
            $responseDetails = $requestResponse->getInfo();

            if (!empty($responseDetails)) {
                /** @var array $shipmentInfo */
                $shipmentInfo = $responseDetails[0];

                if ($shipmentInfo) {
                    /** @var string|null $labelImage */
                    $labelImage = base64_encode($shipmentInfo['label_content']) ?? null;

                    /** @var string $cacheKey */
                    $cacheKey = $this->labelModel->getCacheKey($order);

                    $this->labelModel
                        ->setImage($labelImage)
                        ->setCachedImage($cacheKey, $labelImage);

                    /** @var string|null $trackingNumber */
                    $trackingNumber = $shipmentInfo['tracking_number'] ?? null;

                    $order->addStatusHistoryComment($this->generator->getRmaRequestComment($trackingNumber));
                    $this->orderRepository->save($order);

                    return true;
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return false;
    }

    /**
     * Get carrier model by carrier code.
     *
     * @param string $code
     * @return CarrierInterface
     */
    public function getCarrierModel(string $code = 'ups'): CarrierInterface
    {
        return $this->carrierFactory->create($code);
    }

    /**
     * Get package weight from original shipment.
     *
     * @param Shipment $shipment
     * @param int|string $storeId
     * @return float
     */
    protected function getPackageWeight($shipment, $storeId): float
    {
        /** @var float|null $weight */
        $weight = $shipment->getWeight() ?? $this->configHelper->getPackageWeight($storeId);

        return (float) $weight;
    }

    /**
     * Get first shipment for order.
     *
     * @param OrderInterface $order
     * @return Shipment
     */
    protected function getShipment(OrderInterface $order)
    {
        return $order->getShipmentsCollection()->getFirstItem();
    }

    /**
     * Verify all order items have `return_label_allowed` enabled.
     *
     * @param OrderInterface $order
     * @return bool
     */
    protected function isReturnLabelAllowedForItems(OrderInterface $order): bool
    {
        $items = $order->getAllItems();

        foreach ($items as $item) {
            /** @var Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($item->getProductId());

            /** @var bool $allowed */
            $allowed = (bool) $product->getReturnLabelAllowed();

            if (!$allowed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verify order age is below maximum threshold.
     *
     * @param OrderInterface $order
     * @return bool
     */
    protected function isOrderAgeBelowThreshold(OrderInterface $order): bool
    {
        /** @var int $ageLimit */
        $ageLimit = $this->configHelper->getOrderAgeMaximum($order->getStoreId());

        /** @var \DateTime $createdDateTime */
        $createdDateTime = new \DateTime($order->getCreatedAt());

        /** @var \DateTime $currentDateTime */
        $currentDateTime = new \DateTime();

        return ($ageLimit > 0 && $createdDateTime->diff($currentDateTime)->days <= $ageLimit);
    }

    /**
     * Verify order subtotal meets minimum amount requirements.
     *
     * @param OrderInterface $order
     * @return bool
     */
    protected function isOrderSubtotalAboveMinimum(OrderInterface $order): bool
    {
        /** @var float $minimumAmount */
        $minimumAmount = $this->configHelper->getOrderAmountMinimum($order->getStoreId());

        /** @var float $orderSubtotal */
        $orderSubtotal = (float) $order->getSubtotal();

        return ($minimumAmount > 0 && $minimumAmount <= $orderSubtotal);
    }
}
