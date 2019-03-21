<?php
namespace LiteGoio\LightningPayments\Magento\Framework;

use Magento\Framework\App\CacheInterface;

class Currency extends \Magento\Framework\Currency
{
    public function __construct(
        CacheInterface $appCache,
        $options = null,
        $locale = null
    )
    {
        if ($options == "BTC") {
            $options = array();
            $options["name"] = "BTC";
            $options["currency"] = "BTC";
            $options["symbol"] = "BTC";
            $options["format"] = "¤ #,##0.00";  //enable precession
            $options["precision"] = 8;
        }

        parent::__construct($appCache, $options, $locale);
    }

    public function toCurrency($value = null, array $options = array())
    {
        if($this->_options["currency"] == "BTC")
        {
            //use precession from cached $this->_options
            //$options['precision']=$this->_options["precision"];
            unset($options['precision']);
        }
        
        $currencyStr = trim(parent::toCurrency($value, $options));
        
        if($this->_options["currency"] == "BTC")
        {
            $currencyStr = rtrim(rtrim($currencyStr,"0"),".");
        }
        return $currencyStr;
    }
}

