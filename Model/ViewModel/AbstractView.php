<?php
/**
 * AbstractView.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Model\ViewModel
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\ViewModel;

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Helper\Config as ConfigHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

use function array_replace;

abstract class AbstractView extends DataObject implements ArgumentInterface
{
    /** @var ConfigHelper $configHelper */
    protected $configHelper;

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var RequestInterface $request */
    protected $request;

    /** @var UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct($data);
        $this->configHelper = $configHelper;
        $this->exceptionFactory = $exceptionFactory;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get POST action URL.
     *
     * @param string $route
     * @return string
     */
    public function getPostActionUrl(
        string $route,
        array $params = []
    ): string {
        return $this->urlBuilder->getUrl(
            $route,
            array_replace(
                $params,
                ['_secure' => true]
            )
        );
    }
}
