<?php
namespace LiteGoio\LightningPayments\Model\Config\Backend;

use \Magento\Framework\App\Config\Value as xValue;

class MerchantID extends xValue
{
	public function beforeSave()
    {
        if ($this->getValue() == '') {
            throw new \Magento\Framework\Exception\ValidatorException(__('Litego Merchant ID is required.'));
        }
        
        parent::beforeSave();
    }
}
