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

        $token = $request->getToken();

        $dateTime = date("c");
        $pbx_typeCarte = 'VISA';
        $pbx_typePaiement = 'CARTE';

        $details = array();
        $details['PBX_TYPEPAIEMENT'] = $pbx_typePaiement;
        $details['PBX_TYPECARTE'] = $pbx_typeCarte;
        $details['PBX_TOTAL'] = 999;
        $details['PBX_DEVISE'] = '978';
        $details['PBX_CMD'] = $payment->getNumber();
        $details['PBX_PORTEUR'] = 'marem@smile.fr';
        $details['PBX_REPONDRE_A'] = 'http://www.votre-site.extention/page-de-back-office-site';
        $details['PBX_RETOUR'] = 'Mt:M;Ref:R;Auto:A;error_code:E';
        $details['PBX_EFFECTUE'] = $token->getTargetUrl();
        $details['PBX_ANNULE'] = $token->getTargetUrl();
        $details['PBX_REFUSE'] = $token->getTargetUrl();
        $details['PBX_HASH'] = 'SHA512';
        $details['PBX_TIME'] = $dateTime;

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
