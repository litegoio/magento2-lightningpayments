<?php
namespace LiteGoio\LightningPayments\Model\Config\Backend;

use \Magento\Framework\App\Config\Value as xValue;

use Litego\Litego;

class Secret extends xValue
{
	/**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
	 * @param \Magento\Framework\App\RequestInterface $request
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		\Magento\Framework\App\RequestInterface $request,
		\LiteGoio\LightningPayments\Helper\Data $helper,
        array $data = []
    ) {
        $this->request = $request;
		$this->helper = $helper;
		
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }
	
	
	public function beforeSave()
    {
        if ($this->getValue() == '') {
            throw new \Magento\Framework\Exception\ValidatorException(__('Litego Secret Key is required.'));
        }

        $postData = $this->request->getParams();

        $scope=$this->getScope();
        $scopeId=$this->getScopeId();

        $litego_testnet = is_numeric($this->getFieldsetDataValue('litego_testnet'))
                ?
                $this->getFieldsetDataValue('litego_testnet')
                :
                $this->helper->getAdminConfigValue('litego_testnet',$scope, $scopeId)
        ;

        if($litego_testnet)
        {
            $litego_merchant_id = $this->getFieldsetDataValue('litego_sandbox_merchant_id')
                    ?
                    $this->getFieldsetDataValue('litego_sandbox_merchant_id')
                    :
                    $this->helper->getAdminConfigValue('litego_sandbox_merchant_id',$scope, $scopeId)
            ;

            $litego_secret = $this->getFieldsetDataValue('litego_sandbox_secret')
                    ?
                    $this->getFieldsetDataValue('litego_sandbox_secret')
                    :
                    $this->helper->getAdminConfigValue('litego_sandbox_secret',$scope, $scopeId)
            ;
        }
        else
        {
            $litego_merchant_id = $this->getFieldsetDataValue('litego_merchant_id')
                    ?
                    $this->getFieldsetDataValue('litego_merchant_id')
                    :
                    $this->helper->getAdminConfigValue('litego_merchant_id',$scope, $scopeId)
            ;

            $litego_secret = $this->getFieldsetDataValue('litego_secret')
                    ?
                    $this->getFieldsetDataValue('litego_secret')
                    :
                    $this->helper->getAdminConfigValue('litego_secret',$scope, $scopeId)
            ;
        }

        $mode=Litego::LITEGO_MAINNET_MODE;
        if($litego_testnet)
        {
        	$mode=Litego::LITEGO_TESTNET_MODE;
        }
        $litego = new Litego($mode);

        $result = $litego->authenticate($litego_merchant_id, $litego_secret);

        if($result['code']!=200)
        {
        	throw new \Magento\Framework\Exception\ValidatorException(__('Litego Connection error: '.$result['error_message']));
        }
        parent::beforeSave();
    }
}
