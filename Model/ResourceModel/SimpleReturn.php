<?php
/**
 * SimpleReturn.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ResourceModel;

use AuroraExtensions\SimpleReturns\Shared\ModuleComponentInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SimpleReturn extends AbstractDb implements ModuleComponentInterface
{
    /**
     * Initialize SimpleReturn resource model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            self::SQL_TABLE_ENTITY_RMA,
            self::SQL_COLUMN_RMA_PRIMARY_FIELD
        );
    }
}
