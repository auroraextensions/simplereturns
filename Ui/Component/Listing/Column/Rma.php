<?php
/**
 * Rma.php
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

use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Throwable;

use function __;

class Rma extends Column
{
    public const ACTION_KEY = 'select';
    public const ACTION_LABEL = 'Select';
    public const ENTITY_KEY = 'rma_id';
    public const PARAM_KEY = 'rma_id';
    public const TOKEN_KEY = 'token';

    /** @var string $entityKey */
    private $entityKey;

    /** @var string $paramKey */
    private $paramKey;

    /** @var SimpleReturnRepositoryInterface $rmaRepository */
    private $rmaRepository;

    /** @var string $tokenKey */
    private $tokenKey;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory,
     * @param SimpleReturnRepositoryInterface $rmaRepository
     * @param UrlInterface $urlBuilder
     * @param string|null $entityKey
     * @param string|null $paramKey
     * @param string|null $tokenKey
     * @param array $components
     * @param array $data
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SimpleReturnRepositoryInterface $rmaRepository,
        UrlInterface $urlBuilder,
        string $entityKey = null,
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
        $this->rmaRepository = $rmaRepository;
        $this->urlBuilder = $urlBuilder;
        $this->entityKey = $entityKey ?? static::ENTITY_KEY;
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
                    ?? $this->entityKey;

                if (isset($item[$entityKey])) {
                    /** @var string $actionPath */
                    $actionPath = $this->getData('config/actionPath') ?? '#';

                    /** @var string $paramKey */
                    $paramKey = $this->getData('config/paramKey')
                        ?? $this->paramKey;

                    /** @var string $secretKey */
                    $secretKey = $this->getData('config/secretKey')
                        ?? $this->tokenKey;

                    $item[$this->getData('name')] = [
                        static::ACTION_KEY => [
                            'href' => $this->urlBuilder->getUrl(
                                $actionPath,
                                [
                                    $paramKey => $item[$entityKey],
                                    $secretKey => $this->getSecret((int) $item[$entityKey]),
                                ]
                            ),
                            'label' => __(static::ACTION_LABEL),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param int $rmaId
     * @return string|null
     */
    private function getSecret(int $rmaId): ?string
    {
        try {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->rmaRepository->getById($rmaId);
            return $rma->getToken();
        } catch (Throwable $e) {
            return null;
        }
    }
}
