<?php
namespace Marem\PayumPaybox\Action;

use Marem\PayumPaybox\PayBoxRequestParams;
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

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details[PayBoxRequestParams::PBX_TOTAL] = $payment->getTotalAmount();
        //TODO : dynamise currency code.
        $details[PayBoxRequestParams::PBX_DEVISE] = '978';
        $details[PayBoxRequestParams::PBX_CMD] = $payment->getNumber();
        $details[PayBoxRequestParams::PBX_PORTEUR] = $payment->getClientEmail();
        $token = $request->getToken();
        $details[PayBoxRequestParams::PBX_EFFECTUE] = $token->getTargetUrl();
        $details[PayBoxRequestParams::PBX_ANNULE] = $token->getTargetUrl();
        $details[PayBoxRequestParams::PBX_REFUSE] = $token->getTargetUrl();
        $dateTime = date("c");
        $details[PayBoxRequestParams::PBX_TIME] = $dateTime;

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
