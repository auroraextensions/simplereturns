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
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterface;
use AuroraExtensions\SimpleReturns\Api\Data\SimpleReturnInterfaceFactory;
use AuroraExtensions\SimpleReturns\Api\PackageManagementInterface;
use AuroraExtensions\SimpleReturns\Api\PackageRepositoryInterface;
use AuroraExtensions\SimpleReturns\Api\SimpleReturnRepositoryInterface;
use AuroraExtensions\SimpleReturns\Component\System\ModuleConfigTrait;
use AuroraExtensions\SimpleReturns\Model\Email\Transport\Customer as EmailTransport;
use AuroraExtensions\SimpleReturns\Model\Security\Token as Tokenizer;
use AuroraExtensions\SimpleReturns\Csi\System\Module\ConfigInterface;
use DateTime;
use DateTimeFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime as StdlibDateTime;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Throwable;

use function __;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends Action implements HttpPostActionInterface
{
    use ModuleConfigTrait, RedirectTrait;

    private const PARAM_RMA_ID = 'rma_id';
    private const PARAM_TOKEN = 'token';

    /** @var DateTimeFactory $dateTimeFactory */
    private $dateTimeFactory;

    /** @var EmailTransport $emailTransport */
    private $emailTransport;

    /** @var Escaper $escaper */
    private $escaper;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    private $formKeyValidator;

    /** @var ConfigInterface $moduleConfig */
    private $moduleConfig;

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

    /** @var Json $serializer */
    private $serializer;

    /** @var SimpleReturnInterfaceFactory $simpleReturnFactory */
    private $simpleReturnFactory;

    /** @var SimpleReturnRepositoryInterface $simpleReturnRepository */
    private $simpleReturnRepository;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param DateTimeFactory $dateTimeFactory
     * @param EmailTransport $emailTransport
     * @param Escaper $escaper
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ConfigInterface $moduleConfig
     * @param PackageInterfaceFactory $packageFactory
     * @param PackageManagementInterface $packageManagement
     * @param PackageRepositoryInterface $packageRepository
     * @param RemoteAddress $remoteAddress
     * @param ResultJsonFactory $resultJsonFactory
     * @param Json $serializer
     * @param SimpleReturnInterfaceFactory $simpleReturnFactory
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @param UrlInterface $urlBuilder
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        DateTimeFactory $dateTimeFactory,
        EmailTransport $emailTransport,
        Escaper $escaper,
        ExceptionFactory $exceptionFactory,
        FormKeyValidator $formKeyValidator,
        ConfigInterface $moduleConfig,
        PackageInterfaceFactory $packageFactory,
        PackageManagementInterface $packageManagement,
        PackageRepositoryInterface $packageRepository,
        RemoteAddress $remoteAddress,
        ResultJsonFactory $resultJsonFactory,
        Json $serializer,
        SimpleReturnInterfaceFactory $simpleReturnFactory,
        SimpleReturnRepositoryInterface $simpleReturnRepository,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->dateTimeFactory = $dateTimeFactory;
        $this->emailTransport = $emailTransport;
        $this->escaper = $escaper;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->moduleConfig = $moduleConfig;
        $this->packageFactory = $packageFactory;
        $this->packageManagement = $packageManagement;
        $this->packageRepository = $packageRepository;
        $this->remoteAddress = $remoteAddress;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->simpleReturnFactory = $simpleReturnFactory;
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

        /** @var array $response */
        $response = [];

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        if (!$request->isPost()) {
            $resultJson->setData([
                'error' => true,
                'message' => __('Invalid request type. Must be POST request.'),
            ]);
            return $resultJson;
        }

        if (!$this->formKeyValidator->validate($request)) {
            $resultJson->setData([
                'error' => true,
                'message' => __('Invalid form key.'),
            ]);
            return $resultJson;
        }

        /** @var int|string|null $rmaId */
        $rmaId = $request->getParam(self::PARAM_RMA_ID);
        $rmaId = !empty($rmaId) ? (int) $rmaId : null;

        /** @var string|null $token */
        $token = $request->getParam(self::PARAM_TOKEN);
        $token = !empty($token) ? $token : null;

        /** @var bool $requestLabel */
        $requestLabel = (bool) $request->getPostValue('request_label');

        try {
            /** @var SimpleReturnInterface $rma */
            $rma = $this->simpleReturnRepository->getById($rmaId);
        } catch (NoSuchEntityException $e) {
            $resultJson->setData([
                'error' => true,
                'message' => $e->getMessage(),
            ]);
            return $resultJson;
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
        /* RMA doesn't exist, continue processing. */
        } catch (NoSuchEntityException $e) {
            /** @var PackageInterface $package */
            $package = $this->packageFactory->create();

            /** @var string $remoteIp */
            $remoteIp = $this->remoteAddress
                ->getRemoteAddress();

            /** @var string $carrierCode */
            $carrierCode = $this->getConfig()
                ->getShippingCarrier();

            /** @var string $pkgToken */
            $pkgToken = Tokenizer::createToken();

            /** @var array $pkgData */
            $pkgData = [
                'rma_id'       => $rmaId,
                'carrier_code' => $carrierCode,
                'remote_ip'    => $remoteIp,
                'token'        => $pkgToken,
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
                    'token'   => $pkgToken,
                    '_secure' => true,
                ]
            );
            return $resultJson->setData([
                'success' => true,
                'isSimpleReturnsAjax' => true,
                'message' => __('Successfully created package for return shipment.'),
                'viewUrl' => $viewUrl,
            ]);
        } catch (Throwable $e) {
            $response = [
                'error' => true,
                'message' => $e->getMessage(),
            ];
        }

        $resultJson->setData($response);
        return $resultJson;
    }
}
