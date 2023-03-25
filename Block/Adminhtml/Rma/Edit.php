<?php
/**
 * Edit.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Block\Adminhtml\Rma
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Block\Adminhtml\Rma;

use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;

use function __;
use function is_numeric;

class Edit extends Container
{
    private const PARAM_RMA_ID = 'rma_id';
    private const PARAM_TOKEN = 'token';

    /** @var string $_blockGroup */
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
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        /** @var string|null $token */
        $token = $this->getRequest()->getParam(self::PARAM_TOKEN);
        $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

        if ($rmaId !== null && $token !== null) {
            /** @var string $targetUrl */
            $targetUrl = $this->getUrl(
                'simplereturns/rma/edit',
                [
                    self::PARAM_RMA_ID => $rmaId,
                    self::PARAM_TOKEN => $token,
                ]
            );
            return "(function(){window.location.href='{$targetUrl}';})();";
        }

        return null;
    }
}
