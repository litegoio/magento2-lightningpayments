<?php

namespace LiteGoio\LightningPayments\Block\Adminhtml\Order\View\Info;
use Magento\Payment\Model\Info;

class LitegoPayment extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        array $data = []
    )
    {
        $this->coreRegistry = $registry;
        $this->currencyFactory = $currencyFactory;
        parent::__construct($context, $data);
    }


    /**
     * Retrieve available order
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if ($this->coreRegistry->registry('current_order')) {
            return $this->coreRegistry->registry('current_order');
        }
        if ($this->coreRegistry->registry('order')) {
            return $this->coreRegistry->registry('order');
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the order instance right now.'));
    }

    public function getPayment()
    {
        $order=$this->getOrder();
        $payment = $order->getPayment();
        return $payment;
    }


    public function getPaymentMethod()
    {
        $payment = $this->getPayment();

        $method = $payment->getMethodInstance();

        return $method;
    }

    public function getPaymentCode()
    {
        $method=$this->getPaymentMethod();

        $methodTitle = $method->getTitle();
        $methodCode = $method->getCode();

        return $methodCode;
    }

    public function getDataArray()
    {
        $order=$this->getOrder();

        // Formatted price
        $currencyBase=$order->getBaseCurrency();
        $formatedBasePrice = $currencyBase->formatTxt($order->getBaseGrandTotal());

        $currencyBTC=$this->currencyFactory->create()->load('BTC');
        $formatedBTCPrice = $currencyBTC->formatTxt( $order->getLitegoSatAmount()/100000000 );
        $btcRate=$currencyBTC->formatTxt( $order->getLitegoSatRate()/100000000 );

        return [
            [ 'label' => 'Amount in Base Currency', 'value' => $formatedBasePrice ],
            [ 'label' => 'Amount in BTC',           'value' => $formatedBTCPrice ],
            [ 'label' => 'BTC Rate', 'value' => $btcRate],
        ];
    }
}