<?php
/**
 * View.php
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
    Model\ViewModel\Package\ViewView as ViewModel,
    Shared\Action\Redirector,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\Action\Action,
    App\Action\Context,
    App\Action\HttpGetActionInterface,
    View\Result\PageFactory
};

class View extends Action implements
    HttpGetActionInterface,
    ModuleComponentInterface
{
    /** @see AuroraExtensions\SimpleReturns\Shared\Action\Redirector */
    use Redirector {
        Redirector::__initialize as protected;
    }

    /** @property PageFactory $resultPageFactory */
    protected $resultPageFactory;

    /** @property ViewModel $viewModel */
    protected $viewModel;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ViewModel $viewModel
     * @return void
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ViewModel $viewModel
    ) {
        parent::__construct($context);
        $this->__initialize();
        $this->resultPageFactory = $resultPageFactory;
        $this->viewModel = $viewModel;
    }

    /**
     * Execute simplereturns_rma_view action.
     *
     * @return Page|Redirect
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            __('View Return Package')
        );

        if ($this->viewModel->hasPackage()) {
            return $resultPage;
        }

        return $this->getRedirectToPath(self::ROUTE_SALES_GUEST_VIEW);
    }
}
