<?php
namespace LiteGoio\LightningPayments\Magento\Framework\Locale;

use Magento\Framework\Locale\Format as xFormat;

class Format extends xFormat
{
	public function getCurrencyCode()
	{
		$currency = $this->_scopeResolver->getScope()->getCurrentCurrency();
		$_currencyCode = $currency->getCurrencyCode();
		
		return $_currencyCode;
	}
	
    public function getPriceFormat($localeCode = null, $currencyCode = null)
    {
		
		$localeCode = $localeCode ?: $this->_localeResolver->getLocale();
		$currencyCode = $currencyCode ? $currencyCode : $this->getCurrencyCode();

        $_format = parent::getPriceFormat($localeCode, $currencyCode);
		
        if($currencyCode == 'BTC'){
            //$_format["pattern"]='BTC %s';
			$_format["precision"]="8";
            $_format["requiredPrecision"]="8";
			$_format["decimalSymbol"]='.';
			$_format["groupSymbol"]=',';
			$_format["groupLength"]=3;
			$_format["integerRequired"]=false;
        }
        return $_format;
    }
}
