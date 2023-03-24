<?php
/**
 * LabelManagement.php
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

use AuroraExtensions\SimpleReturns\{
    Api\LabelManagementInterface,
    Api\Data\LabelInterface,
    Api\Data\LabelInterfaceFactory,
    Shared\ModuleComponentInterface
};

class LabelManagement implements LabelManagementInterface, ModuleComponentInterface
{
    /**
     * Get image as data URI.
     *
     * @param LabelInterface $label
     * @return string
     */
    public function getImageDataUri(LabelInterface $label): string
    {
        return self::PREFIX_DATAURI . base64_encode($label->getImage());
    }
}
