<?php
/**
 * CarrierFactory.php
 *
 * Factory for creating shipping carrier models, which
 * requires the ObjectManager for instance generation.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/returns/LICENSE.txt
 *
 * @package       AuroraExtensions_Returns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 * @link          https://devdocs.magento.com/guides/v2.3/extension-dev-guide/factories.html
 */
namespace AuroraExtensions\Returns\Model;

use AuroraExtensions\Returns\{
    Helper\Config as ConfigHelper,
    Shared\DictionaryInterface
};

use Magento\{
    Framework\ObjectManagerInterface,
    Shipping\Model\Carrier\CarrierInterface
};

class CarrierFactory implements DictionaryInterface
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
