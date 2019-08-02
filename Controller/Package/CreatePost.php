<?php
/**
 * CreatePost.php
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

namespace AuroraExtensions\SimpleReturns\Controller\Package;

use AuroraExtensions\SimpleReturns\{
    Api\Data\PackageInterface,
    Api\Data\PackageInterfaceFactory,
    Api\Data\SimpleReturnInterface,
    Api\Data\SimpleReturnInterfaceFactory,
    Api\PackageManagementInterface,
    Api\PackageRepositoryInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Model\AdapterModel\Security\Token as Tokenizer,
    Model\SystemModel\Module\Config as ModuleConfig,
    Shared\Action\Redirector,
    Shared\ModuleComponentInterface
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

class CreatePost extends Action implements
    HttpPostActionInterface,
    ModuleComponentInterface
{
    /** @see AuroraExtensions\SimpleReturns\Shared\Action\Redirector */
    use Redirector {
        Redirector::__initialize as protected;
    }

    /** @property ExceptionFactory $exceptionFactory */
    protected $exceptionFactory;

    /** @property FormKeyValidator $formKeyValidator */
    protected $formKeyValidator;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property PackageInterfaceFactory $packageFactory */
    protected $packageFactory;

    /** @property PackageManagementInterface $packageManagement */
    protected $packageManagement;

    /** @property PackageRepositoryInterface $packageRepository */
    protected $packageRepository;

    /** @property RemoteAddress $remoteAddress */
    protected $remoteAddress;

    /** @property SimpleReturnInterfaceFactory $simpleReturnFactory */
    protected $simpleReturnFactory;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /** @property UrlInterface $urlBuilder */
    protected $urlBuilder;

    /**
     * @param Context $context
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ModuleConfig $moduleConfig
     * @param PackageInterfaceFactory $packageFactory
     * @param PackageManagementInterface $packageManagement
     * @param PackageRepositoryInterface $packageRepository
     * @param RemoteAddress $remoteAddress
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param UrlInterface $urlBuilder
     * @return void
     */
    public function __construct(
        Context $context,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        ModuleConfig $moduleConfig,
        PackageInterfaceFactory $packageFactory,
        PackageManagementInterface $packageManagement,
        PackageRepositoryInterface $packageRepository,
        RemoteAddress $remoteAddress,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->__initialize();
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
     * Execute simplereturns_package_createPost action.
     *
     * @return Redirect
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
        $rmaId = $rmaId !== null && is_numeric($rmaId)
            ? (int) $rmaId
            : null;

        if ($rmaId !== null) {
            /** @var array|null $params */
            $params = $request->getPost('simplereturns_package');

            /** @var string|null $rmaToken */
            $rmaToken = $request->getParam(self::PARAM_TOKEN);
            $rmaToken = !empty($rmaToken) ? $rmaToken : null;

            /** @var int|string|null $requestLabel */
            $requestLabel = $params['request_label'] ?? null;
            $requestLabel = $requestLabel !== null && strtolower($requestLabel) === 'on'
                ? true
                : false;

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
                        $carrierCode = $this->moduleConfig->getShippingCarrier();

                        /** @var array $pkgData */
                        $pkgData = [
                            'rma_id'       => $rmaId,
                            'carrier_code' => $carrierCode,
                            'remote_ip'    => $remoteIp,
                            'token'        => $token,
                        ];

                        /** @var int $pkgId */
                        $pkgId = $this->packageRepository->save(
                            $package->addData($pkgData)
                        );

                        if ($requestLabel) {
                            $this->packageManagement->requestToReturnShipment($package);
                        }

                        /** @var SimpleReturnInterface $rma */
                        $rma = $this->simpleReturnFactory->create();

                        /** @var array $rmaData */
                        $rmaData = [
                            'rma_id'     => $rmaId,
                            'package_id' => $pkgId,
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
                    } catch (AlreadyExistsException $e) {
                        throw $e;
                    } catch (LocalizedException $e) {
                        throw $e;
                    }
                }

                /** @var LocalizedException $exception */
                $exception = $this->exceptionFactory->create(
                    LocalizedException::class,
                    __('Unable to create package for return shipment.')
                );

                throw $exception;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->getRedirectToPath(self::ROUTE_SIMPLERETURNS_RMA_VIEW);
    }
}
