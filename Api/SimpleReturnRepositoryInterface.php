<?php
/**
 * SimpleReturnRepositoryInterface.php
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
use Magento\Sales\Api\Data\OrderInterface;

interface SimpleReturnRepositoryInterface extends AbstractRepositoryInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(OrderInterface $order): Data\SimpleReturnInterface;

    /**
     * @param int $id
     * @return \AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): Data\SimpleReturnInterface;

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface $rma
     * @return int
     */
    public function save(Data\SimpleReturnInterface $rma): int;

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface $rma
     * @return bool
     */
    public function delete(Data\SimpleReturnInterface $rma): bool;

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool;
}
