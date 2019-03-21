<?php
namespace LiteGoio\LightningPayments\Magento\ConfigurableProduct\Block\Product\View\Type;

use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as xConfigurable;

class Configurable extends xConfigurable
{
	public function getJsonConfig()
	{
		$result=parent::getJsonConfig();
		
		$array = json_decode($result, true);
		$format = $this->_localeFormat->getPriceFormat();
		
		$currency = $this->_localeFormat->getCurrencyCode();
		
		//~rt($currency);

		if($currency=="BTC")
		{
			$format['pattern'] = '<span class="currency"><span class="currency-symbol"><i class="cf cf-btc"></i></span> <span class="currency-amount">%s</span></span>';
            $format['precision'] = "6";
            $format['requiredPrecision'] = "6";
		}
		$array['priceFormatHTML'] = $format;
		return json_encode($array);
	}
}