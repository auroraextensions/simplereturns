/**
 * dnd.js
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
define([
    'jquery',
    'dropzone',
    'AuroraExtensions_SimpleReturns/js/url/builder',
    'AuroraExtensions_SimpleReturns/js/url/parser',
    'AuroraExtensions_SimpleReturns/js/utils/dataTypes'
], function ($, Dropzone, urlBuilder, urlParser, dataTypes) {
    'use strict';

    var widget, urn;

    /** @var {Object} widget */
    widget = {
        /** @property {String} name */
        name: 'simpleReturnsDragAndDrop',
        /** @property {String} container */
        container: 'mage',
        /** @property {Object|null} dropzone */
        dropzone: null,
        /**
         * @property {Object} options
         */
        options: {
            createPath: '/simplereturns/rma_attachment/createPost/',
            deletePath: '/simplereturns/rma_attachment/deletePost/',
            files: [],
            formKey: null,
            preload: false,
            rmaId: null,
            searchPath: '/simplereturns/rma_attachment/searchPost/',
            selector: '.dropzone',
            token: null
        },
        /**
         * @return {void}
         */
        _create: function () {
            var createPath, options;

            /** @var {String} createPath */
            createPath = this.options.createPath;

            /** @var {Object} options */
            options = {
                addRemoveLinks: true,
                error: this.onError.bind(this),
                maxFilesize: 100,
                paramName: 'attachments',
                success: this.onFinish.bind(this),
                uploadMultiple: true,
                url: createPath
            };

            /* Prevent Dropzone from attaching twice. */
            Dropzone.autoDiscover = false;

            /* Set dropzone configuration. */
            Dropzone.options.attachmentDropzone = options;

            this.setDropzone(new Dropzone(this.options.selector, options));

            if (this.options.preload) {
                this.preload();
            }

            this.initialize();
        },
        /**
         * @return {void}
         */
        initialize: function () {
            var callback;

            this.options.rmaId = !!this.options.rmaId
                ? this.options.rmaId
                : urlParser.getParamValue('rma_id');

            this.options.token = !!this.options.token
                ? this.options.token
                : urlParser.getParamValue('token');

            /** @var {Function} callback */
            callback = this.onRemovedFile.bind(this);

            this.getDropzone()
                .on('removedfile', callback);
        },
        /**
         * @return {Object}
         */
        getDropzone: function () {
            return this.dropzone;
        },
        /**
         * @param {Object} dropzone
         * @return {this}
         */
        setDropzone: function (dropzone) {
            this.dropzone = dropzone;

            return this;
        },
        /**
         * @return {String}
         */
        getUrn: function () {
            return this.container + '.' + this.name;
        },
        /**
         * @return {void}
         */
        onError: function () {
            /** @todo: Work on implementation. */
        },
        /**
         * @param {File} file
         * @param {String} data
         * @param {ProgressEvent} response
         * @return {void}
         */
        onFinish: function (file, data, response) {
            /** @todo: Work on implementation. */
        },
        /**
         * @return {void}
         */
        preload: function () {
            var blob, buffer,
                dz, mock, files;

            /** @var {Array} files */
            files = this.options.files;

            if (!files.length) {
                return null;
            }

            /** @var {Object} dz */
            dz = this.getDropzone();

            $.each(files, function (key, value) {
                /** @var {Object} mock */
                mock = {
                    name: value.name,
                    size: value.size
                };

                /** @var {Uint8Array} buffer */
                buffer = dataTypes.fromDataUriToBinary(value.blob);

                /** @var {File} blob */
                blob = new File(
                    [buffer],
                    value.name,
                    {
                        type: value.type
                    }
                );

                dz.addFile(blob);
            });
        },
        onPreloadError: function () {
            /** @todo: Work on implementation. */
        },
        /**
         * @param {File} file
         * @return {void}
         */
        onRemovedFile: function (file) {
            /** @todo: Work on implementation. */
        }
    };

    /** @var {String} urn */
    urn = widget.getUrn.call(widget);

    $.widget(urn, widget);

    return $[widget.container][widget.name];
});
