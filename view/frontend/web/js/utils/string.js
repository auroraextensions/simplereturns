/**
 * string.js
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
define([], function () {
    'use strict';

    return {
        /**
         * @param {String} value
         * @param {String} delim
         * @return {String}
         */
        trim: function (value, delim) {
            delim = delim || '/';

            if (value) {
                /* trim-left */
                while (value.charAt(0) === delim) {
                    value = value.slice(1);
                }

                /* trim-right */
                while (value.charAt(value.length - 1) === delim) {
                    value = value.slice(0, value.length - 1);
                }
            }

            return value;
        }
    };
});
