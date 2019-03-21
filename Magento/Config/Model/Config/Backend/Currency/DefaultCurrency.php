<?php
namespace LiteGoio\LightningPayments\Magento\Config\Model\Config\Backend\Currency;

use \Magento\Config\Model\Config\Backend\Currency\DefaultCurrency as xDefaultCurrency;

class DefaultCurrency extends xDefaultCurrency
{
    protected function _getInstalledCurrencies()
    {
        $_installed = parent::_getInstalledCurrencies();
        array_unshift($_installed , 'BTC');
        return $_installed;
    }
}

