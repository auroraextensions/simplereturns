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
 * @package     AuroraExtensions\SimpleReturns\Ui\Component\Listing\Column
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

use function __;

class Actions extends Column
{
    /** @var string $paramKey */
    protected $paramKey;

    /** @var string $tokenKey */
    protected $tokenKey;

    /** @var UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory,
     * @param UrlInterface $urlBuilder
     * @param string $paramKey
     * @param string $tokenKey
     * @param array $components
     * @param array $data
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        string $paramKey = null,
        string $tokenKey = null,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $uiComponentFactory,
            $components,
            $data
        );
        $this->urlBuilder = $urlBuilder;
        $this->paramKey = $paramKey ?? 'entity_id';
        $this->tokenKey = $tokenKey ?? 'token';
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$this->paramKey])) {
                    /** @var string $viewUrlPath */
                    $viewUrlPath = $this->getData('config/viewUrlPath') ?? '#';

                    /** @var string $editUrlPath */
                    $editUrlPath = $this->getData('config/editUrlPath') ?? '#';

                    /** @var string $urlEntityParamName */
                    $urlEntityParamName = $this->getData('config/urlEntityParamName')
                        ?? $this->paramKey;

                    /** @var string $urlSecretParamName */
                    $urlSecretParamName = $this->getData('config/urlSecretParamName')
                        ?? $this->tokenKey;

                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                $viewUrlPath,
                                [
                                    $urlEntityParamName => $item[$urlEntityParamName],
                                    $urlSecretParamName => $item[$urlSecretParamName],
                                ]
                            ),
                            'hidden' => true,
                            'label' => __('View'),
                        ],
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                $editUrlPath,
                                [
                                    $urlEntityParamName => $item[$urlEntityParamName],
                                    $urlSecretParamName => $item[$urlSecretParamName],
                                ]
                            ),
                            'label' => __('Edit'),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
