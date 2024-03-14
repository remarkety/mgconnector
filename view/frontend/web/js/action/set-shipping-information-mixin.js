define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {

            var shippingAddress = quote.shippingAddress();
            if (shippingAddress != undefined) {

                if (shippingAddress['extension_attributes'] === undefined) {
                    shippingAddress['extension_attributes'] = {};
                }
                const rm_email_consent = document.querySelector('[name="extension_attributes[rm_email_consent]"]');
                const rm_sms_consent = document.querySelector('[name="extension_attributes[rm_sms_consent]"]');

                if (rm_email_consent) {
                    shippingAddress['extension_attributes']['rm_email_consent'] = rm_email_consent.checked;
                }
                if (rm_sms_consent) {
                    shippingAddress['extension_attributes']['rm_sms_consent'] = rm_sms_consent.checked;
                }
            }

            return originalAction(messageContainer);
        });
    };
});
