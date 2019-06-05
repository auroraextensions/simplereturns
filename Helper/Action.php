<?php
/**
 * Action.php
 *
 * Helper class for handling action-related parsing tasks.
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

namespace AuroraExtensions\SimpleReturns\Helper;

use AuroraExtensions\SimpleReturns\Shared\ModuleComponentInterface;

class Action implements ModuleComponentInterface
{
    /** @constant int DEFAULT_LENGTH */
    const DEFAULT_LENGTH = 4;

    /**
     * Remove frontName from URL. Assumes
     * prior call to self::getUrlNoScript.
     *
     * @param string $requestUrl
     * @param string $frontName
     * @return string
     */
    public static function getUrlNoFrontName(
        string $requestUrl,
        string $frontName = ''
    ): string
    {
        /* Remove leading slashes or set default frontName value. */
        $frontName = !empty($frontName)
            ? '/' . ltrim($frontName, '/')
            : '/' . self::DEFAULT_FRONT_NAME;

        /** @var int $position */
        $position = stripos($requestUrl, $frontName);

        if ($position !== false) {
            $before = substr($requestUrl, 0, $position);
            $after = substr($requestUrl, ($position + strlen($frontName)));
            $requestUrl = $before . '' . $after;
        }

        return $requestUrl;
    }

    /**
     * Remove route, controller, and action from URL.
     * Assumes prior call to self::getUrlNoScript.
     *
     * @param string $requestUrl
     * @param string $routePath
     * @return string
     */
    public static function getUrlNoRouteControllerAction(
        string $requestUrl,
        string $routePath = ''
    ): string
    {
        if (!empty($routePath)) {
            /** @var int $position */
            $position = strpos($requestUrl, $routePath);

            if ($position !== false) {
                $before = substr($requestUrl, 0, $position);
                $after = substr($requestUrl, ($position + strlen($routePath)));
                $requestUrl = $before . '' . $after;
            }
        }

        return $requestUrl;
    }

    /**
     * Remove script from URL.
     *
     * @param string $requestUrl
     * @param string $scriptName
     * @return string
     */
    public static function getUrlNoScript(
        string $requestUrl,
        string $scriptName = ''
    ): string
    {
        /* Remove leading slashes or set default SCRIPT_NAME value. */
        $scriptName = !empty($scriptName)
            ? '/' . ltrim($scriptName, '/')
            : '/' . self::DEFAULT_SCRIPT_NAME;

        return str_replace($scriptName, '', $requestUrl);
    }

    /**
     * Get array of string components, split at specified delimiter.
     *
     * @param string $value
     * @param string $delimiter
     * @param bool $trim
     * @return array
     */
    public static function getComponents(
        string $value,
        string $delimiter,
        bool $trim = true
    ): array
    {
        /* Trim both ends of $value string, if required. */
        $value = $trim ? trim($value, $delimiter) : $value;

        /** @var array $parts */
        $parts = explode($delimiter, $value);

        return array_values(
            array_filter($parts, 'strlen')
        );
    }

    /**
     * Get array of path components.
     * e.g. '/route/controller/action/' => ['route', 'controller', 'action']
     *
     * @param string $pathInfo
     * @return array
     */
    public static function getPathComponents(string $pathInfo): array
    {
        return self::getComponents($pathInfo, self::PATH_INDEX_DELIMITER);
    }

    /**
     * Get array of full action components.
     * e.g. 'route_controller_action' => ['route', 'controller', 'action']
     *
     * @param string $fullAction
     * @return array
     */
    public static function getFullActionComponents(string $fullAction): array
    {
        return self::getComponents($fullAction, self::FULLACTION_DELIMITER);
    }

    /**
     * Get full action from PATH_INFO.
     *
     * @param string $pathInfo
     * @return string
     */
    public static function getFullActionFromPathInfo(string $pathInfo): string
    {
        /** @var array $parts */
        $parts = self::getPathComponents($pathInfo);

        return implode(self::FULLACTION_DELIMITER, $parts);
    }

    /**
     * Get full action from route path.
     *
     * @param string $routePath
     * @param int $length
     * @return string
     */
    public static function getFullActionFromRoutePath(
        string $routePath,
        int $length = self::DEFAULT_LENGTH
    ): string
    {
        /** @var array $parts */
        $parts = self::getPathComponents($routePath);

        /** @var array $slice */
        $slice = array_slice($parts, 0, $length);

        return implode(self::FULLACTION_DELIMITER, $slice);
    }

    /**
     * Get parsed action parameters from URL.
     * e.g. {<PARAM1>/<VALUE1>/<PARAM2>/<VALUE2>}
     *
     * @param string $params
     * @param int $length
     * @return array|null
     */
    public static function getActionParams(
        string $params,
        int $length = self::DEFAULT_LENGTH
    ): ?array
    {
        /** @var array $parts */
        $parts = array_slice(self::getPathComponents($params), 0, $length);

        if (count($parts) !== $length) {
            return null;
        }

        /** @var array $keys */
        $keys = array_filter($parts, function ($index) {
            return !($index & 1);
        }, ARRAY_FILTER_USE_KEY);

        /** @var array $values */
        $values = array_filter($parts, function ($index) {
            return ($index & 1);
        }, ARRAY_FILTER_USE_KEY);

        return array_combine($keys, $values);
    }

    /**
     * Run actions array through strtolower.
     *
     * @param array $actions
     * @return array
     */
    public static function getLowercasedActions(array $actions = []): array
    {
        return array_map(
            'strtolower',
            $actions
        );
    }
}
