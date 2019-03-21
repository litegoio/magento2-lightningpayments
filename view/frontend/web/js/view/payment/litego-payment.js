define(
	[
		'uiComponent',
		'Magento_Checkout/js/model/payment/renderer-list'
	],
	function (
		Component,
		rendererList
		) {
	
		'use strict';
		rendererList.push(
			{
				type: 'litego_lightning',
				component: 'LiteGoio_LightningPayments/js/view/payment/method-renderer/litego_lightning-method'
			}
		);
		/** Add view logic here if needed */
		return Component.extend({});
	}
);