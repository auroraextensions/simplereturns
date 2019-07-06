<?php
/**
 * ViewView.php
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

namespace AuroraExtensions\SimpleReturns\Model\ViewModel\Rma;

use AuroraExtensions\SimpleReturns\{
    Api\Data\SimpleReturnInterface,
    Api\SimpleReturnRepositoryInterface,
    Exception\ExceptionFactory,
    Helper\Config as ConfigHelper,
    Model\SystemModel\Module\Config as ModuleConfig,
    Model\ViewModel\AbstractView,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\RequestInterface,
    Exception\LocalizedException,
    Exception\NoSuchEntityException,
    Message\ManagerInterface as MessageManagerInterface,
    UrlInterface,
    View\Element\Block\ArgumentInterface
};

class ViewView extends AbstractView implements
    ArgumentInterface,
    ModuleComponentInterface
{
    /** @property MessageManagerInterface $messageManager */
    protected $messageManager;

    /** @property ModuleConfig $moduleConfig */
    protected $moduleConfig;

    /** @property SimpleReturnRepositoryInterface $simpleReturnRepository */
    protected $simpleReturnRepository;

    /**
     * @param ConfigHelper $configHelper
     * @param ExceptionFactory $exceptionFactory
     * @param RequestInterface $request
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @param MessageManagerInterface $messageManager
     * @param ModuleConfig $moduleConfig
     * @param SimpleReturnRepositoryInterface $simpleReturnRepository
     * @return void
     */
    public function __construct(
        ConfigHelper $configHelper,
        ExceptionFactory $exceptionFactory,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = [],
        MessageManagerInterface $messageManager,
        ModuleConfig $moduleConfig,
        SimpleReturnRepositoryInterface $simpleReturnRepository
    ) {
        parent::__construct(
            $configHelper,
            $exceptionFactory,
            $request,
            $urlBuilder,
            $data
        );

        $this->messageManager = $messageManager;
        $this->moduleConfig = $moduleConfig;
        $this->simpleReturnRepository = $simpleReturnRepository;
    }

    /**
     * Get requested SimpleReturn data object.
     *
     * @return SimpleReturnInterface|null
     * @throws NoSuchEntityException
     */
    public function getSimpleReturn(): ?SimpleReturnInterface
    {
        /** @var int|string $rmaId */
        $rmaId = $this->request->getParam(self::PARAM_RMA_ID);
        $rmaId = $rmaId !== null && is_numeric($rmaId)
            ? (int) $rmaId
            : null;

        if (!is_int($rmaId)) {
            return null;
        }

        try {
            return $this->simpleReturnRepository->getById($rmaId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        return null;
    }
}
