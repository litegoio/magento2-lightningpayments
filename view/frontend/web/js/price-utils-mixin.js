define([
    'jquery',
    'underscore'
   ],
function ($, _) {
    'use strict';
    
    return function (target) {
        
        var formatPrice = target.formatPrice;
        
        target.formatPrice = function(amount, format, isShowSign) {
            var result = formatPrice(amount, format, isShowSign);
            //rtrim
            if(format.pattern && format.pattern=='BTC %s')
            {
                result=result.replace(/0+$/,'');
                result=result.replace(/\.+$/,'');
            }
            return result;
        };
        
        return target;
    }
});


