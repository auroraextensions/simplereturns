<?php
/**
 * PackageSearchResultsInterface.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Api\Data
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PackageSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface[]
     */
    public function getItems();

    /**
     * @param \AuroraExtensions\SimpleReturns\Api\Data\PackageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
