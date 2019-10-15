<?php
/**
 * PackageRepositoryInterface.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package        AuroraExtensions_SimpleReturns
 * @copyright      Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license        MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Api;

interface PackageRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface $rma
     * @return \AuroraExtensions\SimpleReturns\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(Data\SimpleReturnInterface $rma): Data\PackageInterface;

    /**
     * @param int $id
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): Data\PackageInterface;

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface $package
     * @return int
     */
    public function save(Data\PackageInterface $package): int;

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface $package
     * @return bool
     */
    public function delete(Data\PackageInterface $package): bool;
}
