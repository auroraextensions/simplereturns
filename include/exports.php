<?php
/**
 * exports.php
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License, which
 * is bundled with this package in the file LICENSE.txt.
 *
 * It is also available on the Internet at the following URL:
 * https://docs.auroraextensions.com/magento/extensions/2.x/simplereturns/LICENSE.txt
 *
 * @package       AuroraExtensions_SimpleReturns
 * @copyright     Copyright (C) 2019 Aurora Extensions <support@auroraextensions.com>
 * @license       MIT License
 */
use Countable;

if (!function_exists('array_key_first')) {
    /** @link https://www.php.net/manual/en/function.array-key-first.php */
    function array_key_first(array $source) {
        foreach($source as $key => $value) {
            return $key;
        }

        return null;
    }
}

if (!function_exists('array_key_last')) {
    /** @link https://www.php.net/manual/en/function.array-key-last.php */
    function array_key_last(array $source) {
        if (!empty($source)) {
            return array_keys($source)[count($source) - 1];
        }

        return null;
    }
}

if (!function_exists('is_countable')) {
    /** @link https://www.php.net/manual/en/function.is-countable.php */
    function is_countable($var) {
        return (is_array($var) || $var instanceof Countable);
    }
}
