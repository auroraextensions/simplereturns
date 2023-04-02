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
 * @package     AuroraExtensions\SimpleReturns\Controller\Adminhtml\Package
 * @copyright   Copyright (C) 2023 Aurora Extensions <support@auroraextensions.com>
 * @license     MIT
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Controller\Adminhtml\Package;

use AuroraExtensions\ModuleComponents\Component\Http\Request\RedirectTrait;
use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\ModuleComponents\Model\Security\HashContext;
use AuroraExtensions\ModuleComponents\Model\Security\HashContextFactory;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\PackageManagementInterface;
use AuroraExtensions\SimpleReturns\Api\PackageRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Component\System\ModuleConfigTrait;
use AuroraExtensions\SimpleReturns\Model\Email\Transport\Customer as EmailTransport;
use AuroraExtensions\SimpleReturns\Csi\System\Module\ConfigInterface;
use DateTime;
use DateTimeFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\UrlInterface;
use Throwable;

use function __;
use function Ramsey\Uuid\v4;

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

    /** @var DateTimeFactory $dateTimeFactory */
    private $dateTimeFactory;

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

    /** @var ResultJsonFactory $resultJsonFactory */
    private $resultJsonFactory;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param DateTimeFactory $dateTimeFactory
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param HashContextFactory $hashContextFactory
     * @param ConfigInterface $moduleConfig
     * @param PackageInterfaceFactory $packageFactory
     * @param PackageManagementInterface $packageManagement
     * @param PackageRepositoryInterface $packageRepository
     * @param RemoteAddress $remoteAddress
     * @param ResultJsonFactory $resultJsonFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param UrlInterface $urlBuilder
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        DateTimeFactory $dateTimeFactory,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        HashContextFactory $hashContextFactory,
        ConfigInterface $moduleConfig,
        PackageInterfaceFactory $packageFactory,
        PackageManagementInterface $packageManagement,
        PackageRepositoryInterface $packageRepository,
        RemoteAddress $remoteAddress,
        ResultJsonFactory $resultJsonFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->dateTimeFactory = $dateTimeFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->hashContextFactory = $hashContextFactory;
        $this->moduleConfig = $moduleConfig;
        $this->packageFactory = $packageFactory;
        $this->packageManagement = $packageManagement;
        $this->packageRepository = $packageRepository;
        $this->remoteAddress = $remoteAddress;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->simpleReturnRepository = $simpleReturnRepository;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var RequestInterface $request */
        $request = $this->getRequest();

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if (!$request->isPost()) {
            return $resultJson->setData([
                'error' => true,
                'message' => __('Invalid request type. Must be POST request.'),
            ]);
        }

        if (!$this->formKeyValidator->validate($request)) {
            return $resultJson->setData([
                'error' => true,
                'message' => __('Invalid form key.'),
            ]);
        }

        /** @var int|string|null $rmaId */
        $rmaId = $request->getParam(self::PARAM_RMA_ID);
        $rmaId = !empty($rmaId) ? (int) $rmaId : null;

        /** @var bool $requestLabel */
        $requestLabel = (bool) $request->getPostValue('request_label');

        try {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->simpleReturnRepository->getById($rmaId);
        } catch (NoSuchEntityException $e) {
            return $resultJson->setData([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        try {
            /** @var PackageInterface $package */
            $package = $this->packageRepository->get($rma);

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
            /** @var string $remoteIp */
            $remoteIp = $this->remoteAddress
                ->getRemoteAddress();

            /** @var string $carrierCode */
            $carrierCode = $this->getConfig()
                ->getShippingCarrier();

            /** @var HashContext $hashContext */
            $hashContext = $this->hashContextFactory->create([
                'data' => null,
                'algo' => 'crc32b',
            ]);

            /** @var string $token */
            $token = (string) $hashContext;

            /** @var PackageInterface $package */
            $package = $this->packageFactory->create();
            $package->addData([
                'uuid' => v4(),
                'rma_id' => $rmaId,
                'carrier_code' => $carrierCode,
                'remote_ip' => $remoteIp,
                'token' => $token,
            ]);

            /** @var int $pkgId */
            $pkgId = $this->packageRepository->save($package);
            $rma->setPackageId($pkgId);
            $this->simpleReturnRepository->save($rma);

            if ($requestLabel) {
                $this->packageManagement->requestToReturnShipment($package);
            }

            /** @var string $viewUrl */
            $viewUrl = $this->urlBuilder->getUrl(
                'simplereturns/package/view',
                [
                    'pkg_id'  => $pkgId,
                    'token'   => $token,
                    '_secure' => true,
                ]
            );
            $resultJson->setData([
                'success' => true,
                'isSimpleReturnsAjax' => true,
                'message' => __('Successfully created package for return shipment.'),
                'viewUrl' => $viewUrl,
            ]);
        } catch (Throwable $e) {
            $resultJson->setData([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
        }

        return $resultJson;
    }
}
