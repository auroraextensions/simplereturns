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

use AuroraExtensions\SimpleReturns\{
    Model\AdapterModel\Security\Token as Tokenizer,
    Shared\ModuleComponentInterface
};
use Magento\Backend\{
    Block\Widget\Context,
    Block\Widget\Container
};

class Edit extends Container implements ModuleComponentInterface
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
        $this->_objectId = 'simplereturns_rma_edit';
        $this->_controller = 'adminhtml_rma';
        $this->setId('simplereturns_rma_edit');

        $this->addButton(
            'simplereturns_rma_edit',
            [
                'class' => 'edit secondary',
                'id' => 'simplereturns-rma-edit',
                'label' => __('Edit'),
                'onclick' => $this->getOnClickJs() ?? '',
            ]
        );
    }

    /**
     * @return string|null
     */
    protected function getOnClickJs(): ?string
    {
        /** @var int|string|null $rmaId */
        $rmaId = $this->getRequest()->getParam(self::PARAM_RMA_ID);
        $rmaId = $rmaId !== null && is_numeric($rmaId)
            ? (int) $rmaId
            : null;

        if ($rmaId !== null) {
            /** @var string|null $token */
            $token = $this->getRequest()->getParam(self::PARAM_TOKEN);
            $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

            if ($token !== null) {
                /** @var string $editUrl */
                $editUrl = $this->getUrl(
                    'simplereturns/rma/edit',
                    [
                        'rma_id' => $rmaId,
                        'token' => $token,
                    ]
                );

                return "(function(){window.location='{$editUrl}';})();";
            }
        }

        return null;
    }
}
