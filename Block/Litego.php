<?php
namespace LiteGoio\LightningPayments\Block;

use Endroid\QrCode\QrCode;

class Litego extends \Magento\Framework\View\Element\Template
{
    protected $coreRegistry;
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;


    protected $template_backup;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Framework\Registry $coreRegistry,
        \LiteGoio\LightningPayments\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->helper = $helper;
        $this->storeManager=$storeManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->formKey = $formKey;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve form key
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
    
    public function setErrorTemplate()
    {
        $this->template_backup=$this->getTemplate();
        $this->setTemplate('litego_error.phtml');
    }
    
    public function setOrder(\Magento\Sales\Model\Order $order)
    {
        $this->order=$order;
        
        if($this->order)
        {
            $this->assign('order', $this->order);
        }
        else
        {
            $this->setErrorTemplate();
        }
    }
        
    public function getOrder()
    {
        return $this->order;
    }
    
    public function getOrderNum()
    {
        return $this->getOrder()->getIncrementId();
    }
    
    public function getAmount()
    {
        // Formatted price
        $order=$this->getOrder();
        $formatedBTCPrice=$this->helper->formatBTC($order->getLitegoSatAmount()/100000000);
        
        return $formatedBTCPrice;
    }
    
    public function getBaseAmount()
    {
        // Formatted price
        $order=$this->getOrder();
        $currencyBase=$order->getBaseCurrency();
        $formatedBasePrice = $currencyBase->formatTxt($order->getBaseGrandTotal());
        
        return $formatedBasePrice;
    }
    
    public function getLitegoInstructionUrl()
    {
        $storeId=$this->helper->getStoreId();
        $litego_testnet=$this->helper->getConfigValue('litego_testnet', $storeId);
        if($litego_testnet)
        {
            return "https://litego.io/openchannel/test";
        }
        else
        {
            return "https://litego.io/openchannel/main";
        }
    }

    public function getQrPaymentRequest()
    {
        $order=$this->getOrder();
        return $order->getLitegoPaymentRequest();
    }
    
    
    public function getQrPaymentRequestImage()
    {
        $order=$this->getOrder();
        $img=$this->getQrCode( "lightning:".$this->getQrPaymentRequest() );
        return 'data:image/png;base64,'.base64_encode($img);
    }
    
    public function getQrCode($text)
    {
        $qrCode = new QrCode($text);
        $qrCode->setWriterByName('png');
        $qrCode->setSize(200);
        return $qrCode->writeString();
    }
    
    public function getDataJson()
    {
        $config = array();
        
        $order = $this->getOrder();
        $config['order'] = $order->getData();

        $date_expire=$order->getLitegoPaymentRequestTimeExpire();
        
        $date_expire=date("U",strtotime($date_expire));
        
        $config['experation'] = ( $date_expire - time() );

        $config['form_key']=$this->formKey->getFormKey();

        return $this->jsonEncoder->encode($config);
    }
}
