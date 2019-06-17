<?php
/**
 * ExceptionFactory.php
 *
 * Factory for generating various exception types.
 * Requires ObjectManager for instance generation.
 *
 * @link https://devdocs.magento.com/guides/v2.3/extension-dev-guide/factories.html
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

namespace AuroraExtensions\SimpleReturns\Exception;

use AuroraExtensions\SimpleReturns\Shared\ModuleComponentInterface;
use Magento\Framework\{
    ObjectManagerInterface,
    Phrase,
    PhraseFactory
};

final class ExceptionFactory implements ModuleComponentInterface
{
    /** @constant string BASE_TYPE */
    const BASE_TYPE = \Exception::class;

    /** @property ObjectManagerInterface $objectManager */
    protected $objectManager;

    /** @property PhraseFactory $phraseFactory */
    protected $phraseFactory;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param PhraseFactory $phraseFactory
     * @return void
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        PhraseFactory $phraseFactory
    )
    {
        $this->objectManager = $objectManager;
        $this->phraseFactory = $phraseFactory;
    }

    /**
     * Create exception from type given.
     *
     * @param string|null $type
     * @param Phrase|null $message
     * @return mixed
     * @throws Exception
     */
    public function create(
        ?string $type = self::BASE_TYPE,
        ?Phrase $message = null
    ) {
        /** @var array $arguments */
        $arguments = [];

        if ($type !== self::BASE_TYPE && !is_subclass_of($type, self::BASE_TYPE)) {
            throw new \Exception(
                __(
                    self::ERROR_INVALID_EXCEPTION_TYPE,
                    $type
                )
            );
        }

        /* If no message was given, set default message. */
        $message = $message ?? $this->phraseFactory->create(
            [
                'text' => self::ERROR_DEFAULT_MESSAGE,
            ]
        );

        if (!is_subclass_of($type, self::BASE_TYPE)) {
            $arguments['message'] = $message->__toString();
        } else {
            $arguments['phrase'] = $message;
        }

        return $this->objectManager->create($type, $arguments);
    }
}
