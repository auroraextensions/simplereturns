<?php
/**
 * SimpleReturn.php
 *
 * SimpleReturn RMA resource model.
 */
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
