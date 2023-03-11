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
use AuroraExtensions\SimpleReturns\{
    Api\Data\PackageInterface,
    Api\Data\PackageInterfaceFactory,
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Api\PackageManagementInterface,
    Api\PackageRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Component\System\ModuleConfigTrait,
    Model\Security\Token as Tokenizer,
    Shared\ModuleComponentInterface,
    Csi\System\Module\ConfigInterface
};
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpPostActionInterface,
    Controller\Result\Redirect as ResultRedirect,
    Data\Form\FormKey\Validator as FormKeyValidator,
    Exception\AlreadyExistsException,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    HTTP\PhpEnvironment\RemoteAddress,
    UrlInterface
};

use function __;
use function is_numeric;
use function strtolower;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    use ModuleConfigTrait, RedirectTrait;

    /** @var ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

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
     * @param FormKeyValidator $formKeyValidator
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
        /** @var Magento\Framework\App\RequestInterface $request */
        $request = $this->getRequest();

        if (!$request->isPost() || !$this->formKeyValidator->validate($request)) {
            return $this->getRedirectToPath(self::ROUTE_SIMPLERETURNS_RMA_VIEW);
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
                        $remoteIp = $this->remoteAddress->getRemoteAddress();

                        /** @var string $token */
                        $token = Tokenizer::createToken();

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
                    } catch (AlreadyExistsException | LocalizedException $e) {
                        throw $e;
                    }
                }

                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class,
                    __('Unable to create package for return shipment.')
                );

                throw $exception;
            } catch (NoSuchEntityException | LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->getRedirectToPath(self::ROUTE_SIMPLERETURNS_RMA_VIEW);
    }
}
