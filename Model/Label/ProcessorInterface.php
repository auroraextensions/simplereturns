<?php
/**
 * ProcessorInterface.php
 *
 * Return label processor interface.
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
 */
namespace AuroraExtensions\Returns\Model\Label;

use Magento\{
    Sales\Api\Data\OrderInterface,
    Shipping\Model\Carrier\CarrierInterface
};

interface ProcessorInterface
{
    /**
     * Request prepaid return label.
     *
     * @param OrderInterface $order
     * @return bool
     */
    public function requestReturnLabel(OrderInterface $order): bool;

    /**
     * Get carrier model by carrier code.
     *
     * @param string $code
     * @return CarrierInterface
     */
    public function getCarrierModel(string $code): CarrierInterface;
}
