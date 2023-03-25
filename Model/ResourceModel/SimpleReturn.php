<?php
/**
 * SimpleReturn.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ResourceModel
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ResourceModel;

use AuroraExtensions\ModuleComponents\Api\AbstractResourceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SimpleReturn extends AbstractDb implements AbstractResourceInterface
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'simplereturns_rma',
            'rma_id'
        );
    }
}
