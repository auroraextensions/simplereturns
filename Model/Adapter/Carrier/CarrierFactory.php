<?php
/**
 * CarrierFactory.php
 *
 * Factory for creating shipping carrier models, which
 * requires the ObjectManager for instance generation.
 *
 * @link https://devdocs.magento.com/guides/v2.3/extension-dev-guide/factories.html
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

namespace AuroraExtensions\SimpleReturns\Model\Carrier;

use AuroraExtensions\SimpleReturns\{
    Helper\Config as ConfigHelper,
    Shared\ModuleComponentInterface
};

use Magento\{
    Framework\ObjectManagerInterface,
    Shipping\Model\Carrier\CarrierInterface
};

class CarrierFactory implements ModuleComponentInterface
{
    /** @property ObjectManagerInterface $objectManager */
    protected $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @return void
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create carrier model via ObjectManager.
     *
     * @param string $code The carrier code.
     * @return CarrierInterface
     * @throws Exception
     */
    public function create(string $code): CarrierInterface
    {
        /** @var array $codes */
        $codes = array_keys(ConfigHelper::$carriers);

        if (!in_array($code, $codes)) {
            throw new \Exception(self::ERROR_INVALID_CARRIER_CODE);
        }

        return $this->objectManager->create(ConfigHelper::$carriers[$code]);
    }
}
