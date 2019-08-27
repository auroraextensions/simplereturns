<?php
/**
 * CancelButton.php
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

namespace AuroraExtensions\SimpleReturns\Ui\Component\Control;

use AuroraExtensions\SimpleReturns\{
    Model\AdapterModel\Security\Token as Tokenizer,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    Escaper,
    UrlInterface,
    View\Element\UiComponent\Control\ButtonProviderInterface
};

class CancelButton implements ButtonProviderInterface, ModuleComponentInterface
{
    /** @property Escaper $escaper */
    protected $escaper;

    /** @property RequestInterface $request */
    protected $request;

    /** @property UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param Escaper $escaper
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @return void
     */
    public function __construct(
        Escaper $escaper,
        RequestInterface $request,
        UrlInterface $urlBuilder
    ) {
        $this->escaper = $escaper;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        /** @var int|string|null $rmaId */
        $rmaId = $this->request->getParam(self::PARAM_RMA_ID);
        $rmaId = $rmaId !== null && is_numeric($rmaId)
            ? (int) $rmaId
            : null;

        if ($rmaId !== null) {
            /** @var string|null $token */
            $token = $this->request->getParam(self::PARAM_TOKEN);
            $token = $token !== null && Tokenizer::isHex($token) ? $token : null;

            if ($token !== null) {
                /** @var string $cancelUrl */
                $cancelUrl = $this->urlBuilder->getUrl(
                    'simplereturns/rma/view',
                    [
                        'rma_id' => $rmaId,
                        'token' => $token,
                    ]
                );

                return [
                    'class' => 'cancel secondary',
                    'label' => __('Cancel'),
                    'onclick' => "(function(){window.location='{$cancelUrl}';})();",
                    'sort_order' => 30,
                ];
            }
        }

        return [];
    }
}
