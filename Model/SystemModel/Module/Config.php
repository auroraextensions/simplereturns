<?php
/**
 * Config.php
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

namespace AuroraExtensions\SimpleReturns\Model\SystemModel\Module;

use Magento\Framework\{
    App\Config\ScopeConfigInterface,
    DataObject,
    DataObject\Factory as DataObjectFactory
};
use Magento\Store\{
    Model\ScopeInterface as StoreScopeInterface,
    Model\Store
};

class Config
{
    /** @constant string DEFAULT_RETURNS_RECIPIENT_FIRST_NAME */
    const DEFAULT_RETURNS_RECIPIENT_FIRST_NAME = 'Customer';

    /** @constant string DEFAULT_RETURNS_RECIPIENT_LAST_NAME */
    const DEFAULT_RETURNS_RECIPIENT_LAST_NAME = 'Service';

    /** @constant int DEFAULT_RETURNS_ORDER_AGE_MAXIMUM */
    const DEFAULT_RETURNS_ORDER_AGE_MAXIMUM = 30;

    /** @constant float DEFAULT_RETURNS_ORDER_AMOUNT_MAXIMUM */
    const DEFAULT_RETURNS_ORDER_AMOUNT_MAXIMUM = 10000.00;

    /** @constant float DEFAULT_RETURNS_ORDER_AMOUNT_MINIMUM */
    const DEFAULT_RETURNS_ORDER_AMOUNT_MINIMUM = 100.00;

    /** @constant string DEFAULT_UPS_CONTAINER_CODE */
    const DEFAULT_UPS_CONTAINER_CODE = 'CP';

    /** @constant string DEFAULT_UPS_METHOD_CODE */
    const DEFAULT_UPS_METHOD_CODE = '03';

    /** @constant float DEFAULT_UPS_PACKAGE_WEIGHT */
    const DEFAULT_UPS_PACKAGE_WEIGHT = 5.00;

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

    /**
     * @return array
     */
    public function getCarriers(): array
    {
        return $this->settings->getData('carriers') ?? [];
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->settings->getData('methods') ?? [];
    }

    /**
     * @return array
     */
    public function getReasons(): array
    {
        return $this->settings->getData('reasons') ?? [];
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
        ) ?? self::DEFAULT_UPS_PACKAGE_WEIGHT;
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
            'simplereturns/returns/recipient_first_name',
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
}
