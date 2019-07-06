<?php
/**
 * Token.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Aurora Extensions EULA,
 * which is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package        AuroraExtensions_SimpleReturns
 * @copyright      Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license        Aurora Extensions EULA
 */
declare(strict_types=1);

namespace AuroraExtensions\SimpleReturns\Model\AdapterModel\Security;

use Magento\Framework\Math\Random as Generator;

class Token
{
    /** @constant string HASH_ALGO */
    const HASH_ALGO = 'sha512';

    /** @property Generator $generator */
    protected $generator;

    /**
     * @param Generator $generator
     * @return void
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Create alphanumeric token of specified length.
     *
     * @param int $length
     * @return string
     */
    public function createToken(int $length = 64): string
    {
        return $this->generator->getRandomString($length);
    }

    /**
     * Get SHA-512 hash value for token.
     *
     * @param string $token
     * @return string
     */
    public static function getHash(string $token): string
    {
        return hash(self::HASH_ALGO, $token);
    }

    /**
     * Test computed hash equality.
     *
     * @param string $one
     * @param string $two
     * @return bool
     */
    public static function isEqual(string $one, string $two): bool
    {
        return hash_equals($one, $two);
    }

    /**
     * Determine if token is hexidecimal.
     *
     * @param string $token
     * @return bool
     */
    public static function isHexidecimal(string $token): bool
    {
        return !preg_match('/[^a-f0-9]/', $token);
    }
}
