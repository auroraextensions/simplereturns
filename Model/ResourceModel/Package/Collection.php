<?php
/**
 * Collection.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ResourceModel\Package
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ResourceModel\Package;

use AuroraExtensions\ModuleComponents\Api\AbstractCollectionInterface;
use AuroraExtensions\SimpleReturns\Model\Data\Package;
use AuroraExtensions\SimpleReturns\Model\ResourceModel\Package as PackageResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection implements AbstractCollectionInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Package::class,
            PackageResource::class
        );
    }
}
