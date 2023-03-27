<?php
/**
 * Actions.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Block\Adminhtml\Rma\Status
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Block\Adminhtml\Rma\Status;

use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use AuroraExtensions\SimpleReturns\Model\System\Module\Config as ModuleConfig;
use Magento\Backend\Block\Widget\Button\SplitButton;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;

use function __;
use function is_numeric;

class Actions extends Container
{
    private const FORM_KEY = 'form_key';
    private const PARAM_RMA_ID = 'rma_id';
    private const PARAM_TOKEN = 'token';

    /** @var string $_blockGroup */
    protected $_blockGroup = 'AuroraExtensions_SimpleReturns';

    /** @var FormKey $formKey */
    protected $formKey;

    /** @var ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /**
     * @param FormKey $formKey
     * @param ModuleConfig $moduleConfig
     * @param Context $context
     * @param array $data
     * @return void
     */
    public function __construct(
        FormKey $formKey,
        ModuleConfig $moduleConfig,
        Context $context,
        array $data = []
    ) {
        $this->formKey = $formKey;
        $this->moduleConfig = $moduleConfig;
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
        $this->_objectId = 'simplereturns_rma_status_actions';
        $this->_controller = 'adminhtml_rma_status';
        $this->setId('simplereturns_rma_status_actions');
        $this->buttonList->add(
            'simplereturns_rma_status_actions',
            [
                'class' => 'actions',
                'class_name' => SplitButton::class,
                'id' => 'simplereturns-rma-status-actions',
                'label' => __('Status'),
                'options' => $this->getStatusOptions(),
            ]
        );
    }

    /**
     * @return array
     */
    private function getStatusOptions(): array
    {
        /** @var array $options */
        $options = [];

        /** @var RequestInterface $request */
        $request = $this->getRequest();

        /** @var int|string|null $rmaId */
        $rmaId = $request->getParam(self::PARAM_RMA_ID);
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        /** @var array $statuses */
        $statuses = $this->moduleConfig->getStatuses();

        /** @var string $name */
        /** @var string $label */
        foreach ($statuses as $name => $label) {
            $options[] = [
                'class' => "action {$name}",
                'data_attribute' => [
                    'mage-init' => [
                        'simpleReturnsRmaEditStatus' => [
                            'actionUrl' => $this->getActionUrl($name),
                            'statusCode' => $name,
                        ],
                    ],
                ],
                'id' => "action-{$name}",
                'label' => __($label),
            ];
        }

        return $options;
    }

    /**
     * @return string|null
     */
    protected function getActionUrl(): ?string
    {
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        /** @var int|string|null $rmaId */
        $rmaId = $request->getParam(self::PARAM_RMA_ID);
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        /** @var string|null $token */
        $token = $request->getParam(self::PARAM_TOKEN);
        $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

        if ($rmaId !== null && $token !== null) {
            return $this->getUrl(
                'simplereturns/rma_status/editPost',
                [
                    self::FORM_KEY => $this->formKey->getFormKey(),
                    self::PARAM_RMA_ID => $rmaId,
                    self::PARAM_TOKEN => $token,
                ]
            );
        }

        return null;
    }
}
