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
    'AuroraExtensions_SimpleReturns/js/url/parser'
], function ($, Dropzone, urlBuilder, urlParser) {
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

            /* Preload existing images. Intended for edit page. */
            if (this.options.preload) {
                options.init = this.preload.bind(this);
            }

            /* Prevent Dropzone from attaching twice. */
            Dropzone.autoDiscover = false;

            /* Set dropzone configuration. */
            Dropzone.options.attachmentDropzone = options;

            this.setDropzone(new Dropzone(this.options.selector, options));
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
            var data, formKey,
                rmaId, settings,
                token, url;

            /** @var {String} rmaId */
            rmaId = this.options.rmaId
                ? this.options.rmaId
                : urlParser.getParamValue('rma_id');

            /** @var {String} token */
            token = this.options.token
                ? this.options.token
                : urlParser.getParamValue('token');

            /** @var {String|null|undefined} formKey */
            formKey = this.options.formKey
                ? this.options.formKey
                : window.FORM_KEY;

            /** @var {Object} data */
            data = {
                'rma_id': rmaId,
                'token': token,
                'form_key': formKey
            };

            /** @var {Object} settings */
            settings = {
                contentType: 'application/json',
                data: JSON.stringify(data),
                method: 'POST',
                error: this.onPreloadError.bind(this),
                success: this.onPreloadResponse.bind(this)
            };

            /** @var {String} url */
            url = this.options.searchPath;

            $.ajax(url, settings);
        },
        onPreloadError: function () {
        },
        /**
         * @param {Object} data
         * @return {void}
         */
        onPreloadResponse: function (data) {
            var dz, file;

            data = data || false;

            if (!data) {
                return null;
            }

            /** @var {Object} dz */
            dz = this.getDropzone();

            $.each(data, function (key, value) {
                /** @var {Object} file */
                file = {
                    name: value.name,
                    size: value.size
                };

                dz.emit('addedfile', file);
                dz.options.thumbnail.call(dz, file, value.path);
                dz.emit('complete', file);
            });
        }
    };

    /** @var {String} urn */
    urn = widget.getUrn.call(widget);

    $.widget(urn, widget);

    return $[widget.container][widget.name];
});
