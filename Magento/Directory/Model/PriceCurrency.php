<?php
namespace LiteGoio\LightningPayments\Magento\Directory\Model;

use Magento\Directory\Model\PriceCurrency as xPriceCurrency;

use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Directory\Model\CurrencyFactory;

class PriceCurrency extends xPriceCurrency
{
    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param Logger $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        Logger $logger
    ) {
        parent::__construct($storeManager, $currencyFactory, $logger);
        $this->localeFormat = $localeFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function convertAndRound($amount, $scope = null, $currency = null, $precision = self::DEFAULT_PRECISION)
    {
        $currentCurrency = $this->getCurrency($scope, $currency);
        $currentCurrencyCode=$currentCurrency->getCurrencyCode();
        
        $convertedValue = $this->getStore($scope)->getBaseCurrency()->convert($amount, $currentCurrency);
        //r($convertedValue);
        if($currentCurrencyCode == "BTC")
        {

            $format=$this->localeFormat->getPriceFormat(null,'BTC');
            $convertedValue=round($convertedValue, $format['precision']);
        }
        else
        {
            $convertedValue=round($convertedValue, $precision);
        }
        return $convertedValue;
    }

    /**
     * Round price
     *
     * @param float $price
     * @return float
     */
    public function round($price)
    {
        return round($price, 8);
    }
}