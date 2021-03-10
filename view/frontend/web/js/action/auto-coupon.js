define([
    'ko',
    'jquery',
    'uiComponent',
    'Magento_SalesRule/js/action/set-coupon-code',
    'Magento_SalesRule/js/model/coupon',
    'Magento_Ui/js/modal/alert'
], function (
    ko,
    $,
    Component,
    setCouponCodeAction,
    coupon,
    alert
) {
    'use strict';

    return Component.extend({
        couponCode: '',

        initialize: function () {
            this._super();

            this.getCouponCode();
            this.applyCoupon();
        },

        getCouponCode: function () {
            let urlParams = new URLSearchParams(window.location.search);
            this.couponCode = urlParams.get('coupon');
        },

        applyCoupon() {
            if (typeof this.couponCode !== 'undefined' && this.couponCode !== null) {
                setCouponCodeAction.registerFailCallback(function (response) {
                    var error = JSON.parse(response.responseText);
                    alert({
                        title: '',
                        content: error.message,
                        actions: {
                            always: function(){
                                window.scrollTo(0,0);
                            }
                        }
                    });
                });
                setCouponCodeAction.registerSuccessCallback(function (response) {
                    window.location = window.location.href.split("?")[0];
                })
                setCouponCodeAction(this.couponCode, coupon.getIsApplied());
            }
        }
    });
});
