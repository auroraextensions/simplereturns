<?php
/**
 * LabelManagementInterface.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package        AuroraExtensions_SimpleReturns
 * @copyright      Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license        Aurora Extensions EULA
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Api;

interface LabelManagementInterface
{
    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\LabelInterface $label
     * @return string
     */
    public function createImageDataUri(Data\LabelInterface $label): string;
}
