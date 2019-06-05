<?php
/**
 * Config.php
 *
 * Helper class for system configuration settings.
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

namespace AuroraExtensions\SimpleReturns\Helper;

use Magento\Framework\{
    App\Config\ScopeConfigInterface,
    DataObject,
    DataObject\Factory as DataObjectFactory
};
use Magento\Store\{
    Model\ScopeInterface as StoreScopeInterface,
    Model\Store
};
use Magento\Ups\Model\Carrier as UpsCarrier;

class Config
{
    /** @constant int DEFAULT_RETURNS_ORDER_AGE_MAXIMUM */
    const DEFAULT_RETURNS_ORDER_AGE_MAXIMUM = 40;

    /** @constant float DEFAULT_RETURNS_ORDER_AMOUNT_MINIMUM */
    const DEFAULT_RETURNS_ORDER_AMOUNT_MINIMUM = 95.00;

    /** @constant string DEFAULT_RETURNS_RECIPIENT_FIRST_NAME */
    const DEFAULT_RETURNS_RECIPIENT_FIRST_NAME = 'Customer';

    /** @constant string DEFAULT_RETURNS_RECIPIENT_LAST_NAME */
    const DEFAULT_RETURNS_RECIPIENT_LAST_NAME = 'Service';

    /** @constant string DEFAULT_UPS_SHIPPING_CONTAINER_CODE */
    const DEFAULT_UPS_SHIPPING_CONTAINER_CODE = 'CP';

    /** @constant string DEFAULT_UPS_SHIPPING_CONTAINER_TYPE */
    const DEFAULT_UPS_SHIPPING_CONTAINER_TYPE = '00';

    /** @constant string DEFAULT_UPS_SHIPPING_METHOD_CODE */
    const DEFAULT_UPS_SHIPPING_METHOD_CODE = '03';

    /** @constant float DEFAULT_UPS_SHIPPING_PACKAGE_WEIGHT */
    const DEFAULT_UPS_SHIPPING_PACKAGE_WEIGHT = 5.00;

    /** @constant string XML_PATH_FIELD_CARRIERS_UPS_CONTAINER */
    const XML_PATH_FIELD_CARRIERS_UPS_CONTAINER = 'carriers/ups/container';

    /** @constant string XML_PATH_FIELD_SYSTEM_STORE_INFORMATION */
    const XML_PATH_FIELD_SYSTEM_STORE_INFORMATION = 'general/store_information';

    /** @constant string XML_PATH_FIELD_GENERAL_MODULE_ENABLE */
    const XML_PATH_FIELD_GENERAL_MODULE_ENABLE = 'auroraextensions_returns/general/enable';

    /** @constant string XML_PATH_FIELD_RETURNS_SHIPPING_CARRIER */
    const XML_PATH_FIELD_RETURNS_SHIPPING_CARRIER = 'auroraextensions_returns/returns/shipping_carrier';

    /** @constant string XML_PATH_FIELD_RETURNS_SHIPPING_METHOD */
    const XML_PATH_FIELD_RETURNS_SHIPPING_METHOD = 'auroraextensions_returns/returns/shipping_method';

    /** @constant string XML_PATH_FIELD_RETURNS_ORDER_AMOUNT_MINIMUM */
    const XML_PATH_FIELD_RETURNS_ORDER_AMOUNT_MINIMUM = 'auroraextensions_returns/returns/order_amount_minimum';

    /** @constant string XML_PATH_FIELD_RETURNS_ORDER_AMOUNT_MAXIMUM */
    const XML_PATH_FIELD_RETURNS_ORDER_AMOUNT_MAXIMUM = 'auroraextensions_returns/returns/order_amount_maximum';

    /** @constant string XML_PATH_FIELD_RETURNS_ORDER_AGE_MAXIMUM */
    const XML_PATH_FIELD_RETURNS_ORDER_AGE_MAXIMUM = 'auroraextensions_returns/returns/order_age_maximum';

    /** @constant string XML_PATH_FIELD_RETURNS_PACKAGE_WEIGHT */
    const XML_PATH_FIELD_RETURNS_PACKAGE_WEIGHT = 'auroraextensions_returns/returns/package_weight';

    /** @constant string XML_PATH_FIELD_RETURNS_RECIPIENT_FIRST_NAME */
    const XML_PATH_FIELD_RETURNS_RECIPIENT_FIRST_NAME = 'auroraextensions_returns/returns/recipient_first_name';

    /** @constant string XML_PATH_FIELD_RETURNS_RECIPIENT_LAST_NAME */
    const XML_PATH_FIELD_RETURNS_RECIPIENT_LAST_NAME = 'auroraextensions_returns/returns/recipient_last_name';

    /** @constant string XML_PATH_FIELD_RETURNS_RETURN_FORM_URL */
    const XML_PATH_FIELD_RETURNS_RETURN_FORM_URL = 'auroraextensions_returns/returns/return_form_url';

    /** @constant string XML_PATH_FIELD_TRANS_EMAIL_SUPPORT_EMAIL */
    const XML_PATH_FIELD_TRANS_EMAIL_SUPPORT_EMAIL = 'trans_email/ident_support/email';

    /** @constant string XML_PATH_FIELD_TRANS_EMAIL_SUPPORT_NAME */
    const XML_PATH_FIELD_TRANS_EMAIL_SUPPORT_NAME = 'trans_email/ident_support/name';

    /** @property array $carriers */
    public static $carriers = [
        UpsCarrier::CODE => UpsCarrier::class,
    ];

    /** @property DataObjectFactory $dataObjectFactory */
    protected $dataObjectFactory;

    /** @property ScopeConfigInterface $scopeConfig */
    protected $scopeConfig;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param ScopeConfigInterface $scopeConfig
     * @return void
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->scopeConfig = $scopeConfig;
    }

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
            self::XML_PATH_FIELD_GENERAL_MODULE_ENABLE,
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
            self::XML_PATH_FIELD_TRANS_EMAIL_SUPPORT_EMAIL,
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
            self::XML_PATH_FIELD_TRANS_EMAIL_SUPPORT_NAME,
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
            self::XML_PATH_FIELD_RETURNS_SHIPPING_CARRIER,
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
            self::XML_PATH_FIELD_RETURNS_SHIPPING_METHOD,
            $scope,
            $store
        ) ?? self::DEFAULT_UPS_SHIPPING_METHOD_CODE;
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
            self::XML_PATH_FIELD_CARRIERS_UPS_CONTAINER,
            $scope,
            $store
        ) ?? self::DEFAULT_UPS_SHIPPING_CONTAINER_CODE;
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
            self::XML_PATH_FIELD_RETURNS_ORDER_AGE_MAXIMUM,
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
            self::XML_PATH_FIELD_RETURNS_ORDER_AMOUNT_MINIMUM,
            $scope,
            $store
        );
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
            self::XML_PATH_FIELD_RETURNS_ORDER_AMOUNT_MAXIMUM,
            $scope,
            $store
        );
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
            self::XML_PATH_FIELD_RETURNS_PACKAGE_WEIGHT,
            $scope,
            $store
        ) ?? self::DEFAULT_UPS_SHIPPING_PACKAGE_WEIGHT;
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
            self::XML_PATH_FIELD_RETURNS_RECIPIENT_FIRST_NAME,
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
            self::XML_PATH_FIELD_RETURNS_RECIPIENT_LAST_NAME,
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
            self::XML_PATH_FIELD_RETURNS_RETURN_FORM_URL,
            $scope,
            $store
        );
    }

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
            self::XML_PATH_FIELD_SYSTEM_STORE_INFORMATION,
            $scope,
            $store
        );

        return $this->dataObjectFactory->create($data);
    }
}
