/**
 * url.js
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
define(['jquery'], function ($) {
    'use strict';

    return {
        /**
         * @param {String} url
         * @param {Object} params
         * @return {String}
         */
        bindParams: function (url, params) {
            var keys, parts;

            /** @var {Array} keys */
            keys = Object.keys(params);

            /** @var {Array} parts */
            parts = url.split('/');
            parts = parts.filter(Boolean);

            $.each(keys, function (key) {
                parts.push(key, params[key]);
            });

            return parts.join('/');
        },
        /**
         * @param {String} url
         * @param {Object} params
         * @return {String}
         */
        getUrl: function (url, params) {
            url = this.trim(url);

            return this.bindParams(url, params);
        },
        /**
         * @param {String} value
         * @param {String} delim
         * @return {String}
         */
        trim: function (value, delim) {
            delim = delim || '/';

            while (value.charAt(0) === delim) {
                value = value.slice(1);
            }

            while (value.charAt(value.length - 1) === delim) {
                value = value.slice(0, value.length - 1);
            }

            return value;
        }
    };
});
