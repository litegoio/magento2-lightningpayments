<?php
namespace LiteGoio\LightningPayments\Magento\Sales\Block\Order\Info;

use Magento\Sales\Block\Order\Info\Buttons as xButtons;

/**
 * ./vendor/magento/module-sales/Block/Order/Info/Buttons.php
 */
class Buttons extends xButtons
{
    protected $_template = 'LiteGoio_LightningPayments::sales/order/info/buttons.phtml';

     /**
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function canPaymentPage($order)
    {
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodCode = $method->getCode();

        if(
            $methodCode == \LiteGoio\LightningPayments\Model\Lightning::PAYMENT_METHOD_CUSTOM_INVOICE_CODE
            and
            $order->getStatus()=="pending"
        )
        {
            return true;
        }
        return false;
    }

    /**
     * Get url for payment page
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getPaymentUrl($order)
    {
        $hash = $order->getLitegoHash();
        $url = $this->getUrl('checkout/setlitego/'.$hash);
        return $url;
    }
}