<?php
/**
 * CreatePost.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT license, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package     AuroraExtensions\SimpleReturns\Controller\Package
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Package;

use AuroraExtensions\ModuleComponents\Component\Http\Request\RedirectTrait;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\ModuleComponents\Model\Security\HashContext;
use AuroraExtensions\ModuleComponents\Model\Security\HashContextFactory;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\PackageManagementInterface;
use AuroraExtensions\SimpleReturns\Api\PackageRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Component\System\ModuleConfigTrait;
use AuroraExtensions\SimpleReturns\Csi\System\Module\ConfigInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\UrlInterface;
use Throwable;

use function __;
use function is_numeric;
use function strtolower;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends Action implements HttpPostActionInterface
{
    /**
     * @var ConfigInterface $moduleConfig
     * @method ConfigInterface getConfig()
     * ---
     * @method Redirect getRedirect()
     * @method Redirect getRedirectToPath()
     * @method Redirect getRedirectToUrl()
     */
    use ModuleConfigTrait, RedirectTrait;

    private const PARAM_RMA_ID = 'rma_id';
    private const PARAM_TOKEN = 'token';
    private const ROUTE_PATH = 'simplereturns/rma/view';

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    private $formKeyValidator;

    /** @var HashContextFactory $hashContextFactory */
    private $hashContextFactory;

    /** @var PackageInterfaceFactory $packageFactory */
    private $packageFactory;

    /** @var PackageManagementInterface $packageManagement */
    private $packageManagement;

    /** @var PackageRepositoryInterface $packageRepository */
    private $packageRepository;

    /** @var RemoteAddress $remoteAddress */
    private $remoteAddress;

    /** @var SimpleReturnInterfaceFactory $simpleReturnFactory */
    private $simpleReturnFactory;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param HashContextFactory $hashContextFactory
     * @param ConfigInterface $moduleConfig
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
        FormKeyValidator $formKeyValidator,
        HashContextFactory $hashContextFactory,
        ConfigInterface $moduleConfig,
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
        $this->formKeyValidator = $formKeyValidator;
        $this->hashContextFactory = $hashContextFactory;
        $this->moduleConfig = $moduleConfig;
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        if (!$request->isPost() || !$this->formKeyValidator->validate($request)) {
            return $this->getRedirectToPath(self::ROUTE_PATH);
        }

        /** @var int|string|null $rmaId */
        $rmaId = $request->getParam(self::PARAM_RMA_ID);
        $rmaId = is_numeric($rmaId) ? (int) $rmaId : null;

        if ($rmaId !== null) {
            /** @var array|null $params */
            $params = $request->getPost('simplereturns_package');

            /** @var string|null $rmaToken */
            $rmaToken = $request->getParam(self::PARAM_TOKEN);
            $rmaToken = !empty($rmaToken) ? $rmaToken : null;

            /** @var int|string|null $requestLabel */
            $requestLabel = $params['request_label'] ?? null;
            $requestLabel = ($requestLabel !== null && strtolower($requestLabel) === 'on');

            try {
                /** @var SimpleReturnInterface $rma */
                $rma = $this->simpleReturnRepository->getById($rmaId);

                if ($rma->getId()) {
                    try {
                        /** @var PackageInterface $package */
                        $package = $this->packageRepository->get($rma);

                        /** @note Consider possible redirect to Package view page. */
                        if ($package->getId()) {
                            /** @var AlreadyExistsException $exception */
                            $exception = $this->exceptionFactory->create(
                                AlreadyExistsException::class,
                                __('There is already a package for this return.')
                            );
                            throw $exception;
                        }
                    /* Package doesn't exist, continue processing. */
                    } catch (NoSuchEntityException $e) {
                        /** @var PackageInterface $package */
                        $package = $this->packageFactory->create();

                        /** @var string $remoteIp */
                        $remoteIp = $this->remoteAddress
                            ->getRemoteAddress();

                        /** @var HashContext $hashContext */
                        $hashContext = $this->hashContextFactory->create(['algo' => 'crc32b']);

                        /** @var string $token */
                        $token = (string) $hashContext;

                        /** @var string $carrierCode */
                        $carrierCode = $this->getConfig()
                            ->getShippingCarrier();

                        /** @var string $methodCode */
                        $methodCode = $this->getConfig()
                            ->getShippingMethod();

                        /** @var array $pkgData */
                        $pkgData = [
                            'rma_id' => $rmaId,
                            'carrier_code' => $carrierCode,
                            'method_code' => $methodCode,
                            'remote_ip' => $remoteIp,
                            'token' => $token,
                        ];

                        /** @var int $pkgId */
                        $pkgId = $this->packageRepository->save(
                            $package->addData($pkgData)
                        );

                        if ($requestLabel) {
                            $this->packageManagement
                                 ->requestToReturnShipment($package);
                        }

                        /** @var SimpleReturnInterface $rma */
                        $rma = $this->simpleReturnFactory->create();

                        /** @var array $rmaData */
                        $rmaData = [
                            'rma_id' => $rmaId,
                            'pkg_id' => $pkgId,
                        ];

                        /* Update RMA with newly created package ID. */
                        $this->simpleReturnRepository->save(
                            $rma->addData($rmaData)
                        );

                        /** @var string $viewUrl */
                        $viewUrl = $this->urlBuilder->getUrl(
                            'simplereturns/package/view',
                            [
                                'pkg_id'  => $pkgId,
                                'token'   => $token,
                                '_secure' => true,
                            ]
                        );
                        return $this->getRedirectToUrl($viewUrl);
                    } catch (Throwable $e) {
                        throw $e;
                    }
                }

                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class,
                    __('Unable to create package for return shipment.')
                );
                throw $exception;
            } catch (Throwable $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->getRedirectToPath(self::ROUTE_PATH);
    }
}
