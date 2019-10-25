<?php
/**
 * SaveButton.php
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

namespace AuroraExtensions\SimpleReturns\Ui\Component\Control;

use AuroraExtensions\SimpleReturns\{
    Model\Security\Token as Tokenizer,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    UrlInterface,
    View\Element\UiComponent\Control\ButtonProviderInterface
};

class SaveButton implements ButtonProviderInterface, ModuleComponentInterface
{
    /** @property RequestInterface $request */
    protected $request;

    /** @property UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @return void
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder
    ) {
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'Magento_Ui/js/form/button-adapter' => [
                        'actions' => [
                            [
                                'actionName' => 'save',
                                'targetName' => 'simplereturns_rma_form.simplereturns_rma_form',
                                'params' => [
                                    true,
                                    [
                                        'order_id' => $this->getOrderId(),
                                        'code' => $this->getProtectCode(),
                                    ]
                                ],
                            ]
                        ],
                    ],
                ],
            ],
            'label' => __('Save'),
            'on_click' => '',
            'sort_order' => 10,
        ];
    }

    /**
     * @return string|null
     */
    protected function getOrderId(): ?string
    {
        /** @var string|null $orderId */
        $orderId = $this->request->getParam(static::PARAM_ORDER_ID);
        $orderId = $orderId !== null && is_numeric($orderId)
            ? $orderId
            : null;

        return $orderId;
    }

    /**
     * @return string|null
     */
    protected function getProtectCode(): ?string
    {
        /** @var string|null $code */
        $code = $this->request->getParam(static::PARAM_PROTECT_CODE);
        $code = $code !== null && Tokenizer::isHex($code)
            ? $code
            : null;

        return $code;
    }
}
