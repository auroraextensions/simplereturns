<?php
/**
 * LabelRepositoryInterface.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package      AuroraExtensions\SimpleReturns\Api
 * @copyright    Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license      MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Api;

use AuroraExtensions\ModuleComponents\Api\AbstractRepositoryInterface;

interface LabelRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface $package
     * @return \AuroraExtensions\SimpleReturns\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(Data\PackageInterface $package): Data\LabelInterface;

    /**
     * @param int $id
     * @return \AuroraExtensions\SimpleReturns\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): Data\LabelInterface;

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\LabelInterface $label
     * @return int
     */
    public function save(Data\LabelInterface $label): int;

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\LabelInterface $label
     * @return bool
     */
    public function delete(Data\LabelInterface $label): bool;
}
