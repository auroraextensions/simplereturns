<?php
/**
 * LabelRepositoryInterface.php
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
namespace AuroraExtensions\SimpleReturns\Api;

use Magento\{
    Framework\Api\SearchCriteriaInterface,
    Sales\Api\Data\OrderInterface
};

interface LabelRepositoryInterface
{
    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \AuroraExtensions\SimpleReturns\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(OrderInterface $order): Data\LabelInterface;

    /**
     * @param int $id
     * @return \AuroraExtensions\SimpleReturns\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id): Data\LabelInterface;

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\LabelInterface $label
     * @return int
     */
    public function save(Data\LabelInterface $label): int;

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return bool
     */
    public function delete(OrderInterface $order): bool;

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById($id): bool;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \AuroraExtensions\SimpleReturns\Api\Data\LabelSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria): Data\LabelSearchResultsInterface;
}
