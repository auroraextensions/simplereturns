<?php
/**
 * Action.php
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

namespace AuroraExtensions\SimpleReturns\Ui\Component\Listing\Column;

use Magento\Framework\{
    UrlInterface,
    View\Element\UiComponent\ContextInterface,
    View\Element\UiComponentFactory
};
use Magento\Ui\Component\Listing\Columns\Column;

class Action extends Column
{
    /** @constant string ACTION_KEY */
    public const ACTION_KEY = 'select';

    /** @constant string ACTION_LABEL */
    public const ACTION_LABEL = 'Select';

    /** @constant string PARAM_KEY */
    public const PARAM_KEY = 'entity_id';

    /** @constant string TOKEN_KEY */
    public const TOKEN_KEY = 'token';

    /** @property string $actionKey */
    protected $actionKey;

    /** @property string $actionLabel */
    protected $actionLabel;

    /** @property string $paramKey */
    protected $paramKey;

    /** @property string $tokenKey */
    protected $tokenKey;

    /** @property UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory,
     * @param array $components
     * @param array $data
     * @param UrlInterface $urlBuilder
     * @param string $actionKey
     * @param string $actionLabel
     * @param string|null $paramKey
     * @param string|null $tokenKey
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        UrlInterface $urlBuilder,
        string $actionKey = null,
        string $actionLabel = null,
        string $paramKey = null,
        string $tokenKey = null
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->urlBuilder = $urlBuilder;
        $this->actionKey = $actionKey ?? static::ACTION_KEY;
        $this->actionLabel = $actionLabel ?? static::ACTION_LABEL;
        $this->paramKey = $paramKey ?? static::PARAM_KEY;
        $this->tokenKey = $tokenKey ?? static::TOKEN_KEY;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            /** @var array $item */
            foreach ($dataSource['data']['items'] as &$item) {
                /** @var string $entityKey */
                $entityKey = $this->getData('config/entityKey')
                    ?? $this->paramKey;

                if (isset($item[$entityKey])) {
                    /** @var string $actionKey */
                    $actionKey = $this->getData('config/actionKey')
                        ?? $this->actionKey;

                    /** @var string $actionLabel */
                    $actionLabel = $this->getData('config/actionLabel')
                        ?? $this->actionLabel;

                    /** @var string $actionPath */
                    $actionPath = $this->getData('config/actionPath') ?? '#';

                    /** @var string $secretKey */
                    $secretKey = $this->getData('config/secretKey')
                        ?? $this->tokenKey;

                    $item[$this->getData('name')] = [
                        $actionKey => [
                            'href' => $this->urlBuilder->getUrl(
                                $actionPath,
                                [
                                    $entityKey => $item[$entityKey],
                                    $secretKey => $item[$secretKey],
                                ]
                            ),
                            'label' => __($actionLabel),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
