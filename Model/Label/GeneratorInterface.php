<?php
/**
 * GeneratorInterface.php
 *
 * Return label generation interface.
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

use Magento\Sales\Api\Data\OrderInterface;

interface GeneratorInterface
{
    /**
     * Create package(s) for shipment.
     *
     * @param OrderInterface $order
     * @return array
     */
    public function createShipmentPackages(OrderInterface $order): array;
}
