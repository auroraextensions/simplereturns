<?php
/**
 * Edit.php
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

namespace AuroraExtensions\SimpleReturns\Block\Adminhtml\Rma;

use Magento\Backend\{
    Block\Widget\Context,
    Block\Widget\Container
};

class Edit extends Container
{
    /** @property string $_blockGroup */
    protected $_blockGroup = 'AuroraExtensions_SimpleReturns';

    /**
     * @param Context $context
     * @param array $data
     * @return void
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_objectId = 'simplereturns_rma_actions';
        $this->_controller = 'adminhtml_rma';
        $this->setId('simplereturns_rma_view');

        $this->addButton(
            'rma_edit',
            [
                'label' => __('Edit'),
                'class' => 'edit',
                'id' => 'simplereturns-rma-edit',
                'data_attribute' => [
                    'url' => 'http://testshop.com/',
                ]
            ]
        );
    }
}
