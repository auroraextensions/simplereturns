<?php
/**
 * Collection.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ResourceModel\Package;

use AuroraExtensions\SimpleReturns\{
    Api\AbstractCollectionInterface,
    Model\Package as PackageDataModel,
    Model\ResourceModel\Package as PackageResourceModel
};
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection implements AbstractCollectionInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            PackageDataModel::class,
            PackageResourceModel::class
        );
    }
}
