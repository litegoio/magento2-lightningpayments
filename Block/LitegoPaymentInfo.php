<?php
namespace LiteGoio\LightningPayments\Block;

class LitegoPaymentInfo extends \Magento\Framework\View\Element\Template
{
    protected $coreRegistry;
    
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;
    
    /**
     * @var Quote|null
     */
    protected $_quote = null;
    
    /**
     * @var array
     */
    protected $_totals;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \LiteGoio\LightningPayments\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->jsonEncoder = $jsonEncoder;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
    }
    
    
    
    /**
     * ./vendor/magento/module-checkout/Block/Cart/Totals.php
     * Get active quote
     *
     * @return Quote
     */
    public function getQuote()
    {
        if ($this->getCustomQuote()) {
            return $this->getCustomQuote();
        }
        
        if (null === $this->_quote) {
            $this->_quote = $this->checkoutSession->getQuote();
        }
        return $this->_quote;
    }
    
    public function getQuoteBaseGrandTotal()
    {
        //return $this->getTotals()['grand_total']->getValue();

        $totals = $this->getTotals();
        $firstTotal = reset( $totals );
        if ($firstTotal) {
            $total = $firstTotal->getAddress()->getBaseGrandTotal();
            return $total;
        }
        return -1;//$this->getQuote()['base_grand_total'];
    }
    
    public function getQuoteBaseCurrency()
    {
        return $this->getQuote()->getBaseCurrencyCode();
    }
    
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getTotals()
    {
        return $this->getTotalsCache();
    }
    

    /**
     * @return array
     */
    public function getTotalsCache()
    {
        if (empty($this->_totals)) {
            if ($this->getQuote()->isVirtual()) {
                $this->_totals = $this->getQuote()->getBillingAddress()->getTotals();
            } else {
                $this->_totals = $this->getQuote()->getShippingAddress()->getTotals();
            }
        }
        return $this->_totals;
    }
    
    public function getDataJson()
    {
        $config = array();

        $config['amount']=$this->getQuoteBaseGrandTotal();
        $config['currency']=$this->getQuoteBaseCurrency();
        
        $config['btc_amount']=$this->helper->convert($config['amount'], $config['currency'], 'BTC');
        $config['btc_rate']=$this->helper->rate($config['currency'], 'BTC');
        
        $config['btc_amount_format'] = $this->helper->formatBTC($config['btc_amount']);
        $config['btc_rate_format'] = $this->helper->formatBTC($config['btc_rate']);
        
        return $this->jsonEncoder->encode($config);
    }
}
