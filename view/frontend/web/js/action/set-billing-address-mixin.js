define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setBillingAddressAction) {

        return wrapper.wrap(setBillingAddressAction, function (originalAction, messageContainer) {

            var billingAddress = quote.billingAddress();
            if (billingAddress != undefined) {

                if (billingAddress['extension_attributes'] === undefined) {
                    billingAddress['extension_attributes'] = {};
                }
                const rm_email_consent = document.querySelector('[name="extension_attributes[rm_email_consent]"]');
                const rm_sms_consent = document.querySelector('[name="extension_attributes[rm_sms_consent]"]');

                if (rm_email_consent) {
                    billingAddress['extension_attributes']['rm_email_consent'] = rm_email_consent.checked;
                }
                if (rm_sms_consent) {
                    billingAddress['extension_attributes']['rm_sms_consent'] = rm_sms_consent.checked;
                }
            }

            return originalAction(messageContainer);
        });
    };
});
