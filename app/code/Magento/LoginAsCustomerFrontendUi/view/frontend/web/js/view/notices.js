/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'mage/translate'
], function ($, Component, customerData) {
    'use strict';

    return Component.extend({

        defaults: {
            isVisible: false
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            var loginAsCustomerData = customerData.get('login-as-customer');

            this.update(loginAsCustomerData());
            loginAsCustomerData.subscribe(function (updatedLoginAsCustomerData) {
                this.update(updatedLoginAsCustomerData);
            }, this);
        },

        /**
         * Update notices content.
         *
         * @param {Object} updatedLoginAsCustomerData
         * @returns void
         */
        update: function (updatedLoginAsCustomerData) {
            this.isVisible(updatedLoginAsCustomerData.adminUserId);
            this.notificationText = $.mage.__('You are connected as <strong>%1</strong> on %2')
                .replace('%1', updatedLoginAsCustomerData.fullname)
                .replace('%2', updatedLoginAsCustomerData.websiteName);
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('isVisible')
                .observe('notificationText');

            return this;
        }
    });
});
