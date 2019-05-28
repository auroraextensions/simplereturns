<?php
/**
 * AbstractRepositoryInterface.php
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

use Magento\Framework\Api\{
    SearchCriteriaInterface,
    SearchResultsInterface,
    Search\FilterGroup
};

interface AbstractRepositoryInterface
{
    /**
     * @param FilterGroup $group
     * @param AbstractCollectionInterface $collection
     * @return void
     */
    public function addFilterGroupToCollection(
        FilterGroup $group,
        AbstractCollectionInterface $collection
    ): void;

    /**
     * @param string $direction
     * @return string
     */
    public function getDirection(string $direction): string;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria): SearchResultsInterface;
}
