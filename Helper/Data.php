<?php
namespace LiteGoio\LightningPayments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

use Litego\Litego;

class Data extends AbstractHelper
{
    const XML_PATH_CONFIG = 'payment/litego_lightning/';

    protected $storeId=null;
    protected $litegoApi=null;
    protected $litegoAuth=null;
    protected $litegoAuthSandbox=null;
    
	/**
     * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     */
    public function __construct(
	    \Magento\Framework\App\Helper\Context $context,
	    \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\Order\Payment\Transaction\Builder $transactionBuilder,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
	)
    {
        parent::__construct($context);
        $this->resourceConfig = $resourceConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->messageManager = $messageManager;
        $this->transactionBuilder = $transactionBuilder;
        $this->currencyFactory = $currencyFactory;
        $this->storeManager = $storeManager;
    }
	
    public function getConfigValue($field, $scopeId = null)
    {
        $scope=ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if($scopeId)
        {
            $scope=ScopeInterface::SCOPE_STORES;
        }
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONFIG . $field, $scope, $scopeId
        );
    }

    public function setConfigValue($field, $value, $scopeId = null)
    {
        $scope=ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        if($scopeId)
        {
            $scope=ScopeInterface::SCOPE_STORES;
        }
        $this->resourceConfig->saveConfig(
            self::XML_PATH_CONFIG . $field,
            $value,
            $scope,
            $scopeId
        );
        $this->cacheTypeList->invalidate(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->cacheTypeList->invalidate(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
    }



    public function getAdminConfigValue($field, $scope=null, $scopeId = null)
    {
        if($scope==null) $scope=ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        return $this->scopeConfig->getValue(
                self::XML_PATH_CONFIG . $field, $scope, $scopeId
        );
    }


    public function getSession()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $objectManager->create('\Magento\Customer\Model\Session');
        return $session;
    }



    public function checkOrderRequest(\Magento\Sales\Model\Order $order)
    {
        $request=$order->getLitegoPaymentRequest();
        if(!$request)
        {
            $result = $this->createLitegoPaymentRequest( $order->getLitegoSatAmount(), $order->getIncrementId() );
            $order->setLitegoPaymentRequest($result['payment_request']);
            $order->setLitegoPaymentRequestTime($result['created']);
            $order->setLitegoPaymentRequestTimeExpire($result['expiry_time']);
            $order->setLitegoChargeId($result['id']);
            $order->save();
        }
        else
        {
            $date=$order->getLitegoPaymentRequestTimeExpire();

            $date=date("U",strtotime($date));

            if($date < time())
            {
                $result = $this->createLitegoPaymentRequest( $order->getLitegoSatAmount(), $order->getIncrementId() );
                $order->setLitegoPaymentRequest($result['payment_request']);
                $order->setLitegoPaymentRequestTime($result['created']);
                $order->setLitegoPaymentRequestTimeExpire($result['expiry_time']);
                $order->setLitegoChargeId($result['id']);
                $order->save();
                $this->messageManager->addSuccessMessage(__('New Lightning payment request generated'));
                
            }
            else
            {
                //r('not expired');
            }
        }

        return $order;
    }


    public function checkOrderPaymentStatus(\Magento\Sales\Model\Order $order)
    {
        if(
            $order->getStatus() == "pending"
        )
        {
            $saveStoreId=$this->getStoreId();
            $this->setStoreId($order->getStoreId());

            $result = $this->checkLitegoPaymentStatus( $order->getLitegoChargeId() );
            
            if($result['paid']==true)
            {
                $this->markOrderAsPaid($order, $result);
            }

            $this->setStoreId($saveStoreId);
        }
        return $order;
    }

    public function createLitegoPaymentRequest($satAmount, $incrementId='')
    {
        $result = $this->getLitegoPaymentRequest($satAmount, $incrementId?'Order '.$incrementId:null);
        
        $result['created_unix']=date("U",strtotime($result['created']));
        $result['expiry_time']=date('c', $result['created_unix'] + $result['expiry_seconds']);
        
        return $result;
    }


    public function setStoreId( $storeId=null )
    {
        $this->storeId=$storeId;
    }

    public function getStoreId()
    {
        if($this->storeId)
        {
            return $this->storeId;
        }
        return $this->storeId=$this->storeManager->getStore()->getId();
    }

    /**
     * Converts the amount value from one currency to another.
     * If the $currencyCodeFrom is not specified the current currency will be used.
     * If the $currencyCodeTo is not specified the base currency will be used.
     * 
     * @param float $amountValue like 13.54
     * @param string|null $currencyCodeFrom like 'USD'
     * @param string|null $currencyCodeTo like 'BYN'
     * @return float
     */
    public function convert($amountValue, $currencyCodeFrom = null, $currencyCodeTo = null)
    {
        $rate=$this->rate($currencyCodeFrom, $currencyCodeTo);

        if($rate==1) return $amountValue;
        
        // Get amount in new currency
        $amountValue = $amountValue * $rate;

        return $amountValue;
    }
    
    
    /**
     * @param string|null $currencyCodeFrom like 'USD'
     * @param string|null $currencyCodeTo like 'BYN'
     * @return float
     */
    public function rate($currencyCodeFrom = null, $currencyCodeTo = null)
    {
        /**
         * If is not specified the currency code from which we want to convert - use current currency
         */
        if (!$currencyCodeFrom) {
            $currencyCodeFrom = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        }

        /**
         * If is not specified the currency code to which we want to convert - use base currency
         */
        if (!$currencyCodeTo) {
            $currencyCodeTo = $this->storeManager->getStore()->getBaseCurrency()->getCode();
        }

        /**
         * Do not convert if currency is same
         */
        if ($currencyCodeFrom == $currencyCodeTo) {
            return 1;
        }

        /** @var float $rate */
        // Get rate
        $rate = $this->currencyFactory->create()->load($currencyCodeFrom)->getAnyRate($currencyCodeTo);
        
        return $rate;
    }
    
    /**
     * 
     * @param float $amount
     * @return string
     */
    public function formatBTC($amount)
    {
        $currencyBTC=$this->currencyFactory->create()->load('BTC');
        $formatedBTCPrice = $currencyBTC->formatTxt( $amount );
        
        return $formatedBTCPrice;
    }
    
    
    
    

    //LITEGO API METHODS
    
    public function getLitegoApi()
    {
        if($this->litegoApi)
        {
            return $this->litegoApi;
        }

        $litego_testnet = $this->getConfigValue('litego_testnet',$this->getStoreId());

        $mode=Litego::LITEGO_MAINNET_MODE;
        if($litego_testnet)
        {
                $mode=Litego::LITEGO_TESTNET_MODE;
        }

        return $this->litegoApi = new Litego($mode);
    }

    public function getLitegoAuth()
    {
        $litego_testnet = $this->getConfigValue('litego_testnet',$this->getStoreId());
        if($litego_testnet)
        {
            if($this->litegoAuthSandbox)
            {
                return $this->litegoAuthSandbox;
            }
            if($this->litegoAuthSandbox=$this->getSession()->getLitegoAuthSandbox())
            {
                return $this->litegoAuthSandbox;
            }
            $litego_merchant_id = $this->getConfigValue('litego_sandbox_merchant_id',$this->getStoreId());
            $litego_secret      = $this->getConfigValue('litego_sandbox_secret',$this->getStoreId());
        }
        else
        {
            if($this->litegoAuth)
            {
                return $this->litegoAuth;
            }
            if($this->litegoAuth=$this->getSession()->getLitegoAuth())
            {
                return $this->litegoAuth;
            }
            $litego_merchant_id = $this->getConfigValue('litego_merchant_id',$this->getStoreId());
            $litego_secret      = $this->getConfigValue('litego_secret',$this->getStoreId());
        }
        
        $litego= $this->getLitegoApi();
        $result = $litego->authenticate($litego_merchant_id, $litego_secret);

        if($result['code']!=200)
        {
            throw new \Magento\Framework\Exception\LocalizedException(__('Litego API error: '.$result['error_message']));
        }
        
        if($litego_testnet)
        {
            $this->getSession()->setLitegoAuthSandbox($result);
            $this->litegoAuthSandbox = $result;
        }
        else
        {
            $this->getSession()->setLitegoAuth($result);
            $this->litegoAuth = $result;
        }

        return $result;
    }
    
    public function getLitegoReauthenticate()
    {
        $litegoAuth = $this->getLitegoAuth();
        $litego= $this->getLitegoApi();
        
        $litego_testnet = $this->getConfigValue('litego_testnet',$this->getStoreId());
        if($litego_testnet)
        {
            $litego_merchant_id = $this->getConfigValue('litego_sandbox_merchant_id',$this->getStoreId());
            $litego_secret      = $this->getConfigValue('litego_sandbox_secret',$this->getStoreId());
        }
        else
        {
            $litego_merchant_id = $this->getConfigValue('litego_merchant_id',$this->getStoreId());
            $litego_secret      = $this->getConfigValue('litego_secret',$this->getStoreId());
        }
        
        try{
            $result = $litego->reauthenticate($litegoAuth['refresh_token'], $litego_merchant_id, $litego_secret);
        }
        catch (\Exception $e)
        {
            throw new \Magento\Framework\Exception\LocalizedException( __('Litego API error: '.$e->getMessage()) );
        }
        
        if($litego_testnet)
        {
            $this->getSession()->setLitegoAuthSandbox($result);
            $this->litegoAuthSandbox = $result;
        }
        else
        {
            $this->getSession()->setLitegoAuth($result);
            $this->litegoAuth = $result;
        }

        return $result;
    }


    public function getLitegoPaymentRequest($satAmount, $description)
    {
        $litegoAuth = $this->getLitegoAuth();
        $litego= $this->getLitegoApi();

        $result = $litego->createCharge($litegoAuth['auth_token'], $description, (double)$satAmount);
        
        if($result['error'] and $result['error_name']=="Forbidden")
        {
            $this->getLitegoReauthenticate();
            $litegoAuth = $this->getLitegoAuth();
            
            $result = $litego->createCharge($litegoAuth['auth_token'], $description, (double)$satAmount);
        }
        
        if($result['error'])
        {
            //TODO: payment of 32 BTC is too large, max payment allowed is 0.04294967 BTC
            throw new \Magento\Framework\Exception\LocalizedException(__('Litego API error: '.$result['error_message']));
        }
        
        return $result;
    }

    public function checkLitegoPaymentStatus($chargeId)
    {
        $litegoAuth = $this->getLitegoAuth();
        $litego= $this->getLitegoApi();

        $result = $litego->getCharge($litegoAuth['auth_token'], $chargeId);
        
        if($result['error'] and $result['error_name']=="Forbidden")
        {
            $this->getLitegoReauthenticate();
            $litegoAuth = $this->getLitegoAuth();
            
            $result = $litego->getCharge($litegoAuth['auth_token'], $chargeId);
        }
        
        if($result['error'])
        {
            throw new \Magento\Framework\Exception\LocalizedException(__('Litego API error: '.$result['error_message']));
        }
        
        return $result;
    }


    public function markOrderAsPaid(\Magento\Sales\Model\Order $order, $paymentData)
    {
        try {
            // Prepare payment object
            $payment = $order->getPayment();
            //$payment->setMethod('litego_lightning');
            $payment->setLastTransId($paymentData['id']);
            $payment->setTransactionId($paymentData['id']);
            $payment->setAdditionalInformation([Transaction::RAW_DETAILS => (array) $paymentData]);

            // Formatted price
            $currencyBase=$order->getBaseCurrency();
            $formatedBasePrice = $currencyBase->formatTxt($order->getBaseGrandTotal());

            $currencyBTC=$this->currencyFactory->create()->load('BTC');
            $formatedBTCPrice = $currencyBTC->formatTxt( $order->getLitegoSatAmount()/100000000 );
            $btcRate=$currencyBTC->formatTxt( $order->getLitegoSatRate()/100000000 );

            // Prepare transaction
            $transaction = $this->transactionBuilder->setPayment($payment)
            ->setOrder($order)
            ->setTransactionId($paymentData['id'])
            ->setAdditionalInformation( [Transaction::RAW_DETAILS => (array) $paymentData] )
            ->setFailSafe(true)
            ->build(Transaction::TYPE_CAPTURE);

            // Add transaction to payment
            $payment->addTransactionCommentsToOrder($transaction, __('The authorized amount is %1. (%2 with Rate %3)', $formatedBasePrice, $formatedBTCPrice, $btcRate));
            $payment->setParentTransactionId(null);


            $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
            $order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
            $order->addStatusToHistory($order->getStatus(), 'Order processed successfully with Litego');

            // Save payment, transaction and order
            $payment->save();
            $order->save();
            $transaction->save();

            return  $transaction->getTransactionId();

        }
        catch (Exception $e)
        {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }
    }
}

