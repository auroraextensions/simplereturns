<?php
/**
 * Package.php
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

namespace AuroraExtensions\SimpleReturns\Model\ResourceModel;

use AuroraExtensions\SimpleReturns\Shared\ModuleComponentInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Package extends AbstractDb implements ModuleComponentInterface
{
    /**
     * Initialize Package resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            self::SQL_TABLE_ENTITY_PKG,
            self::SQL_COLUMN_PKG_PRIMARY_FIELD
        );
    }
}