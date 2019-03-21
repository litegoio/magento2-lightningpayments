<?php
namespace LiteGoio\LightningPayments\Magento\Framework\Locale;

use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Locale\TranslatedLists as xTranslatedLists;

class TranslatedLists extends xTranslatedLists
{
    public function getNewCurrencies() 
    {
        return [
            ['value' => 'BTC', 'label' => 'Bitcoin'],
        ];
    }

    public function getOptionAllCurrencies()
    {
        $currencyBundle = new \Magento\Framework\Locale\Bundle\CurrencyBundle();
        $locale = $this->localeResolver->getLocale();
        $currencies = $currencyBundle->get($locale)['Currencies'] ?: [];

        $options = [];
        foreach ($currencies as $code => $data) {
            $options[] = ['label' => $data[1], 'value' => $code];
        }
        $options = array_merge($options, $this->getNewCurrencies());

        return $this->_sortOptionArray($options);
    }

    public function getOptionCurrencies()
    {
        $currencies = (new CurrencyBundle())->get($this->localeResolver->getLocale())['Currencies'] ?: [];
        $options = [];
        $allowed = $this->_config->getAllowedCurrencies();
        foreach ($currencies as $code => $data) {
            if (!in_array($code, $allowed)) {
                continue;
            }
            $options[] = ['label' => $data[1], 'value' => $code];
        }
        $options = array_merge($options, $this->getNewCurrencies());

        return $this->_sortOptionArray($options);
    }


}