<?php
namespace LiteGoio\LightningPayments\Controller\Litego;
 
use Magento\Framework\App\Action\Context;
 
class Index extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;
	
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    
    /**
     * @var \LiteGoio\LightningPayments\Helper\Data
     */
    protected $helper;
    
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $coreRegistry,
        \LiteGoio\LightningPayments\Helper\Data $helper
	)
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->pageConfig = $pageConfig;
        $this->checkoutSession = $checkoutSession;
        $this->coreRegistry = $coreRegistry;
        $this->helper = $helper;
        parent::__construct($context);
    }
    
    public function getOrder()
    {
        $order=null;
        $order_id=$this->checkoutSession->getLastRealOrderId();

        if(!$order_id)
            throw new \Magento\Framework\Exception\LocalizedException( __('Session error') );
        else
            $order = $this->checkoutSession->getLastRealOrder();
        
        return $order;
    }
    
    public function execute()
    {
        $this->_view->loadLayout();
        $this->pageConfig->getTitle()->set( $this->helper->getConfigValue('title') );
        
        try
        {
            $order = $this->getOrder();

            if(
                $order->getStatus() == \Magento\Sales\Model\Order::STATE_PROCESSING
            )
            {
                if ($order) {
                    //for Magento\Checkout\Model\Session\SuccessValidator
                    $this->checkoutSession
                        ->setLastQuoteId($order->getQuoteId())
                        ->setLastSuccessQuoteId($order->getQuoteId())
                        ->clearHelperData();
                    $this->checkoutSession->setLastOrderId($order->getId())
                        ->setLastRealOrderId($order->getIncrementId())
                        ->setLastOrderStatus( \Magento\Sales\Model\Order::STATE_PROCESSING );
                }
                return $this->_redirect('checkout/onepage/success');
            }
            
        
            $order = $this->helper->checkOrderRequest($order);
            
            $this->_view->getLayout()->getBlock('litego')->setOrder($order);
        }
        catch (\Magento\Framework\Exception\LocalizedException $e)
        {
            $em = $e->getMessage();
            $this->messageManager->addErrorMessage($em);
            $this->_view->getLayout()->getBlock('litego')->setErrorTemplate();
        }
        
        $this->_view->renderLayout();
    }
}