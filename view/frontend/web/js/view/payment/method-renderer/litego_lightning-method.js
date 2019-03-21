define(
	[
		'Magento_Checkout/js/view/payment/default'
	],
	function (Component) {
	'use strict';
		return Component.extend({
			defaults: {
				template: 'LiteGoio_LightningPayments/payment/litego_lightning'
			},
			/** Returns send check to info */
			getMailingAddress: function() {
				return window.checkoutConfig.payment.litego_lightning.mailingAddress;
			},
			/** Returns payable to info */
			/*getPayableTo: function() {
			return window.checkoutConfig.payment.checkmo.payableTo;
			}*/
            
            litegoPaymentInfo: window.litegoPaymentInfo
		});
	}
);