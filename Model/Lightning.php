<?php
namespace LiteGoio\LightningPayments\Model;

class Lightning extends \Magento\Payment\Model\Method\AbstractMethod
{
	const PAYMENT_METHOD_CUSTOM_INVOICE_CODE = 'litego_lightning';
	/**
	* Payment method code
	*
	* @var string
	*/
	protected $_code = self::PAYMENT_METHOD_CUSTOM_INVOICE_CODE;
	
	
	/**
     * Can be used in regular checkout
     *
     * @return bool
     * @deprecated 100.2.0
     */
    public function canUseCheckout()
    {
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('LiteGoio\LightningPayments\Helper\Data');
		$storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
		
		$storeId=$storeManager->getStore()->getId();
		
		$active=$helper->getConfigValue('active', $storeId);
        
        $litego_testnet=$helper->getConfigValue('litego_testnet', $storeId);
        
        if($litego_testnet)
        {
            $merchant_id=$helper->getConfigValue('litego_sandbox_merchant_id', $storeId);
            $secret=$helper->getConfigValue('litego_sandbox_secret', $storeId);
        }
        else
        {
            $merchant_id=$helper->getConfigValue('litego_merchant_id', $storeId);
            $secret=$helper->getConfigValue('litego_secret', $storeId);
        }
		
		if($merchant_id && $secret && $active) return true;
		
        return false;
    }
}