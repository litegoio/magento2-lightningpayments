<?php
namespace LiteGoio\LightningPayments\Observer;

use Psr\Log\LoggerInterface;

class Order implements \Magento\Framework\Event\ObserverInterface
{

    public function __construct(
        \Magento\Framework\Registry $registry, 
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \LiteGoio\LightningPayments\Helper\Data $helper,
        LoggerInterface $logger
    )
    {
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->registry = $registry;
		$this->currencyFactory = $currencyFactory;
		$this->logger = $logger;
        $this->helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    { 
        $orderId = $observer->getEvent()->getOrderIds();

        $this->logger->info( 
            "orderId: ".
            print_r($orderId,1)
        );

        $base_url = $this->storeManager->getStore()->getBaseUrl();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('\Magento\Sales\Model\Order') ->load($orderId[0]);
        $payment = $order->getPayment();


        $method = $payment->getMethodInstance();
        $methodTitle = $method->getTitle();

        $methodCode = $method->getCode();

        $this->logger->info( "methodCode: ".print_r($methodCode,1) );

        $order_data= $order->getData();
        $status = $this->checkoutSession->getLastOrderStatus();

        $increment_id = $order_data['increment_id'];

        $this->logger->info( "increment_id: ".print_r($increment_id,1) );

        //$order->getState() == \Magento\Sales\Model\Order::STATE_NEW
        if(
            $methodCode == \LiteGoio\LightningPayments\Model\Lightning::PAYMENT_METHOD_CUSTOM_INVOICE_CODE 
            and
            $order->getStatus() == 'pending'
        )
        {    
            $fromCode=$order->getBaseCurrencyCode();
            $toCode='BTC';
            $amount=$order->getBaseGrandTotal();

            $btc_amount=$this->helper->convert($amount, $fromCode, $toCode);
            $btc_rate=$this->helper->rate($fromCode, $toCode);

            $btc_sat_amount = $btc_amount * 100000000;
            $btc_sat_rate = $btc_rate * 100000000;
            
            $this->logger->info( "fromCode: ".print_r($fromCode,1) );
            
            $this->logger->info( "toCode: ".print_r($toCode,1) );
             
            $this->logger->info( "amount: ".print_r($amount,1) );
            
            $this->logger->info( "btc_amount: ".print_r($btc_amount,1) );
            
            $this->logger->info( "btc_rate: ".print_r($btc_rate,1) );
            
            $this->logger->info( "btc_sat_amount: ".print_r($btc_sat_amount,1) );
            
            $this->logger->info( "btc_sat_rate: ".print_r($btc_sat_rate,1) );
            
            $order->setLitegoSatRate($btc_sat_rate);
            $order->setLitegoSatAmount($btc_sat_amount);
            $order->setLitegoHash(uniqid( $order->getId() ));
            $order->save();
            
            $redirect = $objectManager->get('\Magento\Framework\App\Response\Http');
            $redirect->setRedirect($base_url.'checkout/litego/');

        }
    }

}

