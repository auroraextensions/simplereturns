<?php
/**
 * Redirector.php
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
namespace AuroraExtensions\SimpleReturns\Shared\Action;

use AuroraExtensions\SimpleReturns\{
    Exception\TraitContextException,
    Shared\ModuleComponentInterface
};
use Magento\Framework\{
    App\Action\AbstractAction,
    Controller\Result\Redirect
};

trait Redirector
{
    /**
     * Trait initializer. Permit usage only
     * by classes extending AbstractAction.
     *
     * @return void
     * @throws TraitContextException
     */
    public function __initialize()
    {
        if (!is_subclass_of(static::class, AbstractAction::class)) {
            throw new TraitContextException(
                __(
                    ModuleComponentInterface::ERROR_INVALID_TRAIT_CONTEXT,
                    __TRAIT__,
                    AbstractAction::class
                )
            );
        }
    }

    /**
     * Get result redirect instance.
     *
     * @return Redirect
     */
    public function getRedirect(): Redirect
    {
        return $this->resultRedirectFactory->create();
    }

    /**
     * Get result redirect instance, with redirect path set.
     *
     * @param string $path
     * @return Redirect
     */
    public function getRedirectToPath(string $path = '*'): Redirect
    {
        /** @var Redirect $redirect */
        $redirect = $this->getRedirect();
        $redirect->setPath($path);

        return $redirect;
    }

    /**
     * Get result redirect instance, with redirect URL set.
     *
     * @param string $url
     * @return Redirect
     */
    public function getRedirectToUrl(string $url = '*'): Redirect
    {
        /** @var Redirect $redirect */
        $redirect = $this->getRedirect();
        $redirect->setUrl($url);

        return $redirect;
    }
}
