<?php
/**
 * Config.php
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

namespace AuroraExtensions\SimpleReturns\Model\SystemModel\Module;

use Magento\Dhl\Model\Carrier as DHL;
use Magento\Fedex\Model\Carrier as Fedex;
use Magento\Framework\{
    App\Config\ScopeConfigInterface,
    DataObject,
    DataObject\Factory as DataObjectFactory
};
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\{
    Model\Information as StoreInformation,
    Model\ScopeInterface as StoreScopeInterface,
    Model\Store
};
use Magento\Ups\Model\Carrier as UPS;
use Magento\Usps\Model\Carrier as USPS;

class Config
{
    /** @constant string DEFAULT_RETURNS_RECIPIENT_FIRST_NAME */
    public const DEFAULT_RETURNS_RECIPIENT_FIRST_NAME = 'Customer';

    /** @constant string DEFAULT_RETURNS_RECIPIENT_LAST_NAME */
    public const DEFAULT_RETURNS_RECIPIENT_LAST_NAME = 'Service';

    /** @constant int DEFAULT_RETURNS_ORDER_AGE_MAXIMUM */
    public const DEFAULT_RETURNS_ORDER_AGE_MAXIMUM = 30;

    /** @constant float DEFAULT_RETURNS_ORDER_AMOUNT_MAXIMUM */
    public const DEFAULT_RETURNS_ORDER_AMOUNT_MAXIMUM = 1000.00;

    /** @constant float DEFAULT_RETURNS_ORDER_AMOUNT_MINIMUM */
    public const DEFAULT_RETURNS_ORDER_AMOUNT_MINIMUM = 50.00;

    /** @constant float DEFAULT_PACKAGE_WEIGHT */
    public const DEFAULT_PACKAGE_WEIGHT = 5.00;

    /** @constant string DEFAULT_RMA_STATUS_CODE */
    public const DEFAULT_RMA_STATUS_CODE = 'pending';

    /** @constant string DEFAULT_FEDEX_CONTAINER_CODE */
    public const DEFAULT_FEDEX_CONTAINER_CODE = 'FEDEX_BOX';

    /** @constant string DEFAULT_FEDEX_METHOD_CODE */
    public const DEFAULT_FEDEX_METHOD_CODE = 'FEDEX_GROUND';

    /** @constant string DEFAULT_UPS_CONTAINER_CODE */
    public const DEFAULT_UPS_CONTAINER_CODE = '00';

    /** @constant string DEFAULT_UPS_METHOD_CODE */
    public const DEFAULT_UPS_METHOD_CODE = '03';

    /** @var array $containerMethods */
    protected $containerMethods = [
        DHL::CODE   => 'getDhlContainer',
        Fedex::CODE => 'getFedexContainer',
        UPS::CODE   => 'getUpsContainer',
        USPS::CODE  => 'getUspsContainer',
    ];

    /** @property DataObjectFactory $dataObjectFactory */
    protected $dataObjectFactory;

    /** @property ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /** @property DataObject $settings */
    protected $settings;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     * @return void
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $scopeConfig;
        $this->settings = $this->dataObjectFactory->create($data);
    }

    /* DI data methods */

    /**
     * @return array
     */
    public function getCarriers(): array
    {
        return $this->getSettings()->getData('carriers') ?? [];
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->getSettings()->getData('methods') ?? [];
    }

    /**
     * @return array
     */
    public function getReasons(): array
    {
        return $this->getSettings()->getData('reasons') ?? [];
    }

    /**
     * @return array
     */
    public function getResolutions(): array
    {
        return $this->getSettings()->getData('resolutions') ?? [];
    }

    /**
     * @return array
     */
    public function getStatuses(): array
    {
        return $this->getSettings()->getData('statuses') ?? [];
    }

    /**
     * @return DataObject|null
     */
    public function getSettings(): ?DataObject
    {
        return $this->settings;
    }

    /* System configuration settings */

    /**
     * Check if module is enabled for given store.
     *
     * @param int|string $store
     * @param string $scope
     * @return bool
     */
    public function isModuleEnabled(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            'simplereturns/general/enable',
            $scope,
            $store
        );
    }

    /**
     * Get customer support email address.
     *
     * @param int|string $store
     * @param string $scope
     * @return string
     */
    public function getCustomerSupportEmail(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_support/email',
            $scope,
            $store
        );
    }

    /**
     * Get customer support name.
     *
     * @param int|string $store
     * @param string $scope
     * @return string
     */
    public function getCustomerSupportName(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_support/name',
            $scope,
            $store
        );
    }

    /**
     * Get shipping carrier code.
     *
     * @param int|string $store
     * @param string $scope
     * @return string
     */
    public function getShippingCarrier(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'simplereturns/returns/shipping_carrier',
            $scope,
            $store
        );
    }

    /**
     * Get carrier shipping method.
     *
     * @param int|string $store
     * @param string $scope
     * @return string
     */
    public function getShippingMethod(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'simplereturns/returns/shipping_method',
            $scope,
            $store
        );
    }

    /**
     * @param string $code
     * @param int $store
     * @param string $scope
     * @return string
     */
    public function getContainerCode(
        string $code,
        int $store = Store::DEFAULT_STORE_ID,
        string $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        /** @var string $method */
        $method = $this->containerMethods[$code];

        return $this->{$method}($store, $scope);
    }

    /**
     * Get shipping packaging for Fedex shipping method.
     *
     * @param int|string $store
     * @param string $scope
     * @return string
     */
    public function getFedexContainer(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'carriers/fedex/container',
            $scope,
            $store
        ) ?? self::DEFAULT_FEDEX_CONTAINER_CODE;
    }

    /**
     * Get shipping packaging for UPS shipping method.
     *
     * @param int|string $store
     * @param string $scope
     * @return string
     */
    public function getUpsContainer(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'carriers/ups/container',
            $scope,
            $store
        ) ?? self::DEFAULT_UPS_CONTAINER_CODE;
    }

    /**
     * Get order age maximum from configuration settings.
     *
     * @param int|string $store
     * @param string $scope
     * @return int
     */
    public function getOrderAgeMaximum(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): int
    {
        return (int) $this->scopeConfig->getValue(
            'simplereturns/returns/order_age_maximum',
            $scope,
            $store
        ) ?? self::DEFAULT_RETURNS_ORDER_AGE_MAXIMUM;
    }

    /**
     * Get order amount minimum from configuration settings.
     *
     * @param int|string $store
     * @param string $scope
     * @return float
     */
    public function getOrderAmountMinimum(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): float
    {
        return (float) $this->scopeConfig->getValue(
            'simplereturns/returns/order_amount_minimum',
            $scope,
            $store
        ) ?? self::DEFAULT_RETURNS_ORDER_AMOUNT_MINIMUM;
    }

    /**
     * Get order amount maximum from configuration settings.
     *
     * @param int|string $store
     * @param string $scope
     * @return float
     */
    public function getOrderAmountMaximum(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): float
    {
        return (float) $this->scopeConfig->getValue(
            'simplereturns/returns/order_amount_maximum',
            $scope,
            $store
        ) ?? self::DEFAULT_RETURNS_ORDER_AMOUNT_MAXIMUM;
    }

    /**
     * Get default package weight for prepaid return label.
     *
     * @param int|string $store
     * @param string $scope
     * @return float
     */
    public function getPackageWeight(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): float
    {
        return (float) $this->scopeConfig->getValue(
            'simplereturns/returns/package_weight',
            $scope,
            $store
        ) ?? self::DEFAULT_PACKAGE_WEIGHT;
    }

    /**
     * Get recipient first name for return label.
     *
     * @param int|string $store
     * @param string $scope
     * @return string
     */
    public function getRecipientFirstName(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'simplereturns/returns/recipient_first_name',
            $scope,
            $store
        ) ?? self::DEFAULT_RETURNS_RECIPIENT_FIRST_NAME;
    }

    /**
     * Get recipient last name for return label.
     *
     * @param int|string $store
     * @param string $scope
     * @return string
     */
    public function getRecipientLastName(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'simplereturns/returns/recipient_last_name',
            $scope,
            $store
        ) ?? self::DEFAULT_RETURNS_RECIPIENT_LAST_NAME;
    }

    /**
     * Get recipient full name for return label.
     *
     * @param int|string $store
     * @param string $scope
     * @return string
     */
    public function getRecipientFullName(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        /** @var string $firstName */
        $firstName = $this->getRecipientFirstName($store, $scope);

        /** @var string $lastName */
        $lastName = $this->getRecipientLastName($store, $scope);

        return $firstName . ' ' . $lastName;
    }

    /**
     * Get recipient information as DataObject.
     *
     * @param int|string $store
     * @param string $scope
     * @return DataObject
     */
    public function getRecipientData(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): DataObject
    {
        /** @var array $data */
        $data = [
            'first_name' => $this->getRecipientFirstName($store, $scope),
            'last_name'  => $this->getRecipientLastName($store, $scope),
            'full_name'  => $this->getRecipientFullName($store, $scope),
        ];

        return $this->dataObjectFactory->create($data);
    }

    /**
     * Get URL to store returns form.
     *
     * @param int|string $store
     * @param string $scope
     * @return string
     */
    public function getReturnFormUrl(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): string
    {
        return $this->scopeConfig->getValue(
            'simplereturns/returns/return_form_url',
            $scope,
            $store
        );
    }

    /* Origin Settings */

    /**
     * Get company name from settings.
     *
     * @param int $store
     * @param string $scope
     * @return string|null
     */
    public function getCompanyName(
        int $store = Store::DEFAULT_STORE_ID,
        string $scope = StoreScopeInterface::SCOPE_STORE
    ): ?string
    {
        return $this->scopeConfig->getValue(
            'simplereturns/origin/company',
            $scope,
            $store
        );
    }

    /* Miscellaneous */

    /**
     * Get store information as DataObject.
     *
     * @param int|string $store
     * @param string $scope
     * @return DataObject
     */
    public function getStoreInfo(
        $store = Store::DEFAULT_STORE_ID,
        $scope = StoreScopeInterface::SCOPE_STORE
    ): DataObject
    {
        /** @var array $data */
        $data = (array) $this->scopeConfig->getValue(
            'general/store_information',
            $scope,
            $store
        );

        return $this->dataObjectFactory->create($data);
    }

    /**
     * Get origin information as DataObject.
     *
     * @param int $store
     * @param string $scope
     * @return DataObject
     */
    public function getOriginInfo(
        int $store = Store::DEFAULT_STORE_ID,
        string $scope = StoreScopeInterface::SCOPE_STORE
    ): DataObject
    {
        /** @var array $data */
        $data = [
            'company' => $this->getCompanyName(
                $store,
                $scope
            ) ?? $this->scopeConfig->getValue(
                StoreInformation::XML_PATH_STORE_INFO_NAME,
                $scope,
                $store
            ),
            'email' => $this->getCustomerSupportEmail(
                $store,
                $scope
            ),
            'street' => $this->scopeConfig->getValue(
                'shipping/origin/street_line1',
                $scope,
                $store
            ),
            'street2' => $this->scopeConfig->getValue(
                'shipping/origin/street_line2',
                $scope,
                $store
            ),
            'city' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_CITY,
                $scope,
                $store
            ),
            'postcode' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_POSTCODE,
                $scope,
                $store
            ),
            'region_id' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_REGION_ID,
                $scope,
                $store
            ),
            'country_id' => $this->scopeConfig->getValue(
                ShippingConfig::XML_PATH_ORIGIN_COUNTRY_ID,
                $scope,
                $store
            ),
        ];

        return $this->dataObjectFactory->create($data);
    }
}
