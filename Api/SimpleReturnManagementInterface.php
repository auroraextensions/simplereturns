<?php
/**
 * SimpleReturnManagementInterface.php
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

use AuroraExtensions\SimpleReturns\{
    Api\Data\PackageInterface,
    Api\Data\SimpleReturnInterface
};

interface SimpleReturnManagementInterface
{
    /**
     * Add status update comment to return.
     *
     * @param string $comment
     * @return bool
     */
    public function addComment(string $comment): bool;

    /**
     * Create shipment package.
     *
     * @param \AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface $rma
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface|null
     */
    public function createPackage(SimpleReturnInterface $rma): ?PackageInterface;
}
