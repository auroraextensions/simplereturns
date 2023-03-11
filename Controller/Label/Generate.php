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
use AuroraExtensions\SimpleReturns\{
    Api\Data\PackageInterface,
    Api\Data\PackageInterfaceFactory,
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Api\PackageManagementInterface,
    Api\PackageRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\Http\Request\InvalidTokenException,
    Model\Security\Token as Tokenizer,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpGetActionInterface,
    Controller\Result\Redirect as ResultRedirect,
    Exception\AlreadyExistsException,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    HTTP\PhpEnvironment\RemoteAddress,
    UrlInterface
};

use function is_numeric;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Generate extends Action implements
    HttpGetActionInterface,
    ModuleComponentInterface
{
    use RedirectTrait;

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var PackageInterfaceFactory $packageFactory */
    protected $packageFactory;

    /** @var PackageManagementInterface $packageManagement */
    protected $packageManagement;

    /** @var PackageRepositoryInterface $packageRepository */
    protected $packageRepository;

    /** @var RemoteAddress $remoteAddress */
    protected $remoteAddress;

    /** @var SimpleReturnInterfaceFactory $simpleReturnFactory */
    protected $simpleReturnFactory;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @var UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param ExceptionFactory $exceptionFactory
     * @param PackageInterfaceFactory $packageFactory
     * @param PackageManagementInterface $packageManagement
     * @param PackageRepositoryInterface $packageRepository
     * @param RemoteAddress $remoteAddress
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param UrlInterface $urlBuilder
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ExceptionFactory $exceptionFactory,
        PackageInterfaceFactory $packageFactory,
        PackageManagementInterface $packageManagement,
        PackageRepositoryInterface $packageRepository,
        RemoteAddress $remoteAddress,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->exceptionFactory = $exceptionFactory;
        $this->packageFactory = $packageFactory;
        $this->packageManagement = $packageManagement;
        $this->packageRepository = $packageRepository;
        $this->remoteAddress = $remoteAddress;
        $this->simpleReturnFactory = $simpleReturnFactory;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        if (!$request->isGet()) {
            return $this->getRedirectToPath(self::ROUTE_SIMPLERETURNS_PKG_VIEW);
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
                    'simplereturns/package/view',
                    $params
                );
                return $this->getRedirectToUrl($viewUrl);
            } catch (InvalidTokenException | NoSuchEntityException | LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->getRedirectToPath(self::ROUTE_SIMPLERETURNS_PKG_VIEW);
    }
}
