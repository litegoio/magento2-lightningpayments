define([
	'jquery',
        'clipboard',
        'progressTimer',
        'Magento_Customer/js/customer-data',
        'domReady!'
], function($, ClipboardJS, progressTimer, customerData) 
    {
        "use strict";
        
        return function(config)
        {
            var clipboard = new ClipboardJS(config.clipboard);

            $(config.progressElement).progressTimer({
                timeLimit: config.data.experation,
                warningThreshold: config.data.experation - (config.data.experation/10*7),
                completeThreshold: config.data.experation - (config.data.experation/10*9),
                baseStyle: 'progress-bar-info',
                warningStyle: 'progress-bar-warning',
                completeStyle: 'progress-bar-danger',
                onFinish: function() {
                    //reload page for generate new lightning payment request
                    location.reload();
                }
            });

            var prev_error=0;

            var getorderstatus_interval = setInterval( function(){ 
                $.ajax({
                    'url': BASE_URL + 'checkout/getorderstatus/'+config.data.order.litego_hash,
                    'type': 'GET',
                    async: true,
                    dataType: 'json',
                    data: { 'form_key': config.data.form_key },
                    beforeSend: function() {
                        //console.log('observe get');
                    },
                    error: function(data) {
                        //console.log('error', data);
                        customerData.reload('messages');
                    },
                    'success' : function(data) {
                        //console.log('success', data);
                        if(data.error)
                        {
                            prev_error=1;
                            customerData.reload('messages');
                        }
                        else if(prev_error)
                        {
                            prev_error=0;
                            customerData.reload('messages');
                        }
                        //window.clearInterval(getorderstatus_interval);
                        
                        if(data.order.status == "processing") location.reload();
                    }
                });

            }, 3000);

            
            
        };
    }
);