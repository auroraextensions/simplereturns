<?php
/**
 * Save.php
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

use AuroraExtensions\SimpleReturns\{
    Model\AdapterModel\Security\Token as Tokenizer,
    Shared\ModuleComponentInterface
};
use Magento\Backend\{
    Block\Widget\Context,
    Block\Widget\Container
};

class Save extends Container implements ModuleComponentInterface
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
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->_objectId = 'simplereturns_rma_save';
        $this->_controller = 'adminhtml_rma';
        $this->setId('simplereturns_rma_save');

        $this->addButton(
            'simplereturns_rma_save',
            [
                'class' => 'save primary',
                'id' => 'simplereturns-rma-save',
                'label' => __('Save'),
                'type' => 'submit',
                'form' => 'adminhtml-simplereturns-rma-edit',
            ]
        );
    }
}
