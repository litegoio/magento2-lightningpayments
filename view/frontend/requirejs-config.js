var config = {
    paths: {
        "clipboard": "LiteGoio_LightningPayments/js/external/clipboard",
        "progressTimer": "LiteGoio_LightningPayments/js/external/jquery.progressTimer"
    },
    shim: {
        'progressTimer': {
            deps: ['jquery']
        },
        'clipboard': {
            exports: 'ClipboardJS',
            deps: ['jquery']
        }
    },
    'config': {
        'mixins': {
            'Magento_Catalog/js/price-utils': {
                'LiteGoio_LightningPayments/js/price-utils-mixin': true
            }
        }
    }
}


