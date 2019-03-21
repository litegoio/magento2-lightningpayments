<?php
namespace LiteGoio\LightningPayments\Controller\Litego;

use Magento\Framework\App\Action\Context;

class SetLitego extends \Magento\Framework\App\Action\Action
{
    protected $_resultPageFactory;

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

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;
    
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \LiteGoio\LightningPayments\Helper\Data $helper
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->coreRegistry = $coreRegistry;
        $this->messageManager=$messageManager;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $base_url = $this->storeManager->getStore()->getBaseUrl();

        $hash=$this->getRequest()->getParam('hash',false);
        $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->loadByAttribute("litego_hash",$hash);
        
        if(!$order->getId())
        {
            $this->messageManager->addErrorMessage(__("Order not found"));
            return $this->resultRedirectFactory->create()->setPath('*/cart/');
        }

        if($order->getStatus()!="pending")
        {
            $this->messageManager->addErrorMessage(__("Order status error"));
            return $this->resultRedirectFactory->create()->setPath('*/cart/');
        }

        $this->checkoutSession
            ->setLastQuoteId($order->getQuoteId())
            ->setLastSuccessQuoteId($order->getQuoteId())
            ->clearHelperData();
        $this->checkoutSession->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus( $order->getStatus() );

        return $this->_redirect( $base_url.'checkout/litego/' );
    }
}