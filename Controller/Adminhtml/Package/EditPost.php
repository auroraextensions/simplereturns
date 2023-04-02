<?php
/**
 * EditPost.php
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

use AuroraExtensions\ModuleComponents\Exception\ExceptionFactory;
use AuroraExtensions\SimpleReturns\Api\Data\PackageInterface;
use AuroraExtensions\SimpleReturns\Api\PackageManagementInterface;
use AuroraExtensions\SimpleReturns\Api\PackageRepositoryInterface;
use AuroraExtensions\SimpleReturns\Component\System\ModuleConfigTrait;
use AuroraExtensions\SimpleReturns\Csi\System\Module\ConfigInterface;
use DateTime;
use DateTimeFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\UrlInterface;
use Throwable;

use function __;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditPost extends Action implements HttpPostActionInterface
{
    /**
     * @var ConfigInterface $moduleConfig
     * @method ConfigInterface getConfig()
     */
    use ModuleConfigTrait;

    private const PARAM_PKG_ID = 'pkg_id';
    private const PARAM_TOKEN = 'token';

    /** @var DateTimeFactory $dateTimeFactory */
    private $dateTimeFactory;

    /** @var ExceptionFactory $exceptionFactory */
    private $exceptionFactory;

    /** @var FormKeyValidator $formKeyValidator */
    private $formKeyValidator;

    /** @var PackageManagementInterface $packageManagement */
    private $packageManagement;

    /** @var PackageRepositoryInterface $packageRepository */
    private $packageRepository;

    /** @var ResultJsonFactory $resultJsonFactory */
    private $resultJsonFactory;

    /** @var UrlInterface $urlBuilder */
    private $urlBuilder;

    /**
     * @param Context $context
     * @param DateTimeFactory $dateTimeFactory
     * @param ExceptionFactory $exceptionFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ConfigInterface $moduleConfig
     * @param PackageManagementInterface $packageManagement
     * @param PackageRepositoryInterface $packageRepository
     * @param ResultJsonFactory $resultJsonFactory
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
        ConfigInterface $moduleConfig,
        PackageManagementInterface $packageManagement,
        PackageRepositoryInterface $packageRepository,
        ResultJsonFactory $resultJsonFactory,
        UrlInterface $urlBuilder
    ) {
        parent::__construct($context);
        $this->dateTimeFactory = $dateTimeFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->moduleConfig = $moduleConfig;
        $this->packageManagement = $packageManagement;
        $this->packageRepository = $packageRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return ResultJson
     */
    public function execute()
    {
        /** @var RequestInterface $request */
        $request = $this->getRequest();

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

        /** @var int|string|null $pkgId */
        $pkgId = $request->getParam(self::PARAM_PKG_ID);
        $pkgId = !empty($pkgId) ? (int) $pkgId : null;

        /** @var string|null $token */
        $token = $request->getParam(self::PARAM_TOKEN);
        $token = !empty($token) ? $token : null;

        /** @var bool $requestLabel */
        $requestLabel = (bool) $request->getPostValue('request_label');

        try {
            /** @var PackageInterface $package */
            $package = $this->packageRepository->getById($pkgId);

            if ($requestLabel) {
                if ($package->getLabelId()) {
                    /** @var AlreadyExistsException $exception */
                    $exception = $this->exceptionFactory->create(
                        AlreadyExistsException::class,
                        __('There is already a label for this package.')
                    );
                    throw $exception;
                }

                /** @var DateTime $dateTime */
                $dateTime = $this->dateTimeFactory->create();
                $package->setUpdatedAt((string) $dateTime);
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
                'message' => __('Successfully updated package for return shipment.'),
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
