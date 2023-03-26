<?php
/**
 * Generate.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Controller\Label
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Label;

use AuroraExtensions\ModuleComponents\Component\Http\Request\RedirectTrait;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Api\PackageManagementInterface;
use AuroraExtensions\SimpleReturns\Api\PackageRepositoryInterface;
use AuroraExtensions\SimpleReturns\Exception\Http\Request\InvalidTokenException;
use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\UrlInterface;
use Throwable;

use function is_numeric;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Generate extends Action implements HttpGetActionInterface
{
    /**
     * @method ResultRedirect getRedirect()
     * @method ResultRedirect getRedirectToPath()
     * @method ResultRedirect getRedirectToUrl()
     */
    use RedirectTrait;

    private const PARAM_PKG_ID = 'pkg_id';
    private const PARAM_TOKEN = 'token';
    private const ROUTE_PATH = 'simplereturns/package/view';

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var PackageManagementInterface $packageManagement */
    private $packageManagement;

    /** @var PackageRepositoryInterface $packageRepository */
    private $packageRepository;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param ExceptionFactory $exceptionFactory
     * @param PackageManagementInterface $packageManagement
     * @param PackageRepositoryInterface $packageRepository
     * @param UrlInterface $urlBuilder
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ExceptionFactory $exceptionFactory,
        PackageManagementInterface $packageManagement,
        PackageRepositoryInterface $packageRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->exceptionFactory = $exceptionFactory;
        $this->packageManagement = $packageManagement;
        $this->packageRepository = $packageRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        if (!$request->isGet()) {
            return $this->getRedirectToPath(self::ROUTE_PATH);
        }

        /** @var int|string|null $pkgId */
        $pkgId = $request->getParam(self::PARAM_PKG_ID);
        $pkgId = is_numeric($pkgId) ? (int) $pkgId : null;

        if ($pkgId !== null) {
            /** @var array $params */
            $params = ['_secure' => true];

            /** @var string|null $token */
            $token = $request->getParam(self::PARAM_TOKEN);
            $token = !empty($token) ? $token : null;

            try {
                /** @var PackageInterface $package */
                $package = $this->packageRepository->getById($pkgId);

                if (!Tokenizer::isEqual($token, $package->getToken())) {
                    /** @var InvalidTokenException $exception */
                    $exception = $this->exceptionFactory->create(
                        InvalidTokenException::class
                    );
                    throw $exception;
                }

                /* Create RMA request and generate shipping label. */
                if ($this->packageManagement->requestToReturnShipment($package)) {
                    $params += [
                        'pkg_id' => $package->getId(),
                        'token' => $package->getToken(),
                    ];
                }

                /** @var string $viewUrl */
                $viewUrl = $this->urlBuilder->getUrl(
                    self::ROUTE_PATH,
                    $params
                );
                return $this->getRedirectToUrl($viewUrl);
            } catch (Throwable $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->getRedirectToPath(self::ROUTE_PATH);
    }
}
