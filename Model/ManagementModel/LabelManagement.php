<?php
/**
 * LabelManagement.php
 *
 * Return shipment label management model.
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
    Api\LabelManagementInterface,
    Api\Data\LabelInterface,
    Api\Data\LabelInterfaceFactory,
    Shared\ModuleComponentInterface
};

class LabelManagement implements LabelManagementInterface, ModuleComponentInterface
{
    /**
     * Create image data URI from blob.
     *
     * @param LabelInterface $label
     * @return string
     */
    public function createImageDataUri(LabelInterface $label): string
    {
        return '';
    }
}
