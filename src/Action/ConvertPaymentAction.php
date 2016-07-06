<?php
namespace Marem\PayumPaybox\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

class ConvertPaymentAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();


        $details = array();
        $details['PBX_TOTAL'] = $payment->getTotalAmount();
        //TODO : dynamise currency code.
        $details['PBX_DEVISE'] = '978';
        $details['PBX_CMD'] = $payment->getNumber();
        $details['PBX_PORTEUR'] = $payment->getClientEmail();
        $token = $request->getToken();
        $details['PBX_EFFECTUE'] = $token->getTargetUrl();
        $details['PBX_ANNULE'] = $token->getTargetUrl();
        $details['PBX_REFUSE'] = $token->getTargetUrl();
        $details['PBX_HASH'] = 'SHA512';
        $dateTime = date("c");
        $details['PBX_TIME'] = $dateTime;


        $paymentDetails = $payment->getDetails();
        array_merge($details, $paymentDetails);

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
