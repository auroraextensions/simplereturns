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
 * https://docs.auroraextensions.com/magento/extensions/2.x/returns/LICENSE.txt
 *
 * @package       AuroraExtensions_Returns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       Aurora Extensions EULA
 */
namespace AuroraExtensions\Returns\Shared\Action;

use AuroraExtensions\Returns\{
    Exception\TraitException,
    Shared\DictionaryInterface
};

use Magento\Framework\{
    App\Action\AbstractAction as Base,
    Controller\Result\Redirect
};

trait Redirector
{
    /**
     * Trait initializer. Permit usage only
     * by classes extending AbstractAction.
     *
     * @return void
     * @throws TraitException
     */
    public function __initialize()
    {
        if (!is_subclass_of(static::class, Base::class)) {
            throw new TraitException(
                __(
                    DictionaryInterface::ERROR_INVALID_TRAIT_CONTEXT,
                    __TRAIT__,
                    Base::class
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
