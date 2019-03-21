<?php
namespace LiteGoio\LightningPayments\Controller\Litego;

use Magento\Framework\App\Action\Context;

class GetOrderStatus extends \Magento\Framework\App\Action\Action
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
        \LiteGoio\LightningPayments\Helper\Data $helper
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->checkoutSession = $checkoutSession;
        $this->coreRegistry = $coreRegistry;
        $this->messageManager=$messageManager;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $result=array();
        $result['error']=0;

        if (!$this->getRequest()->isAjax())
        {
            return $this->resultRedirectFactory->create()->setPath('*/cart/');
        }

        if (!$this->getFormKeyValidator()->validate($this->getRequest()))
        {
            $this->messageManager->addErrorMessage(__("CSRF Error. Refresh page"));
            $result['error']=1;
            return $this->jResponse($result);
        }

        $params = $this->getRequest()->getParams();
        $hash=$this->getRequest()->getParam('hash',false);

        $result['hash']=$hash;

        $order = $this->_objectManager->create('\Magento\Sales\Model\Order')->loadByAttribute("litego_hash",$hash);
        
        if(!$order->getId())
        {
            $this->messageManager->addErrorMessage(__("Order not found"));
            $result['error']=1;
            return $this->jResponse($result);
        }

        $this->helper->checkOrderPaymentStatus($order);

        $order = $order->load($order->getId());

        $result['order']=$order->getData();

        return $this->jResponse($result);

    }


    public function jResponse($result)
    {
        return $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }

    /**
     * @return \Magento\Framework\Data\Form\FormKey\Validator
     * @deprecated 100.0.9
     */
    private function getFormKeyValidator()
    {
        if (!$this->formKeyValidator) {
            $this->formKeyValidator = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Data\Form\FormKey\Validator::class);
        }
        return $this->formKeyValidator;
    }

}