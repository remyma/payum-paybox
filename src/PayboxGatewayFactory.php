<?php
namespace Marem\PayumPaybox;

use Marem\PayumPaybox\Action\AuthorizeAction;
use Marem\PayumPaybox\Action\CancelAction;
use Marem\PayumPaybox\Action\ConvertPaymentAction;
use Marem\PayumPaybox\Action\CaptureAction;
use Marem\PayumPaybox\Action\NotifyAction;
use Marem\PayumPaybox\Action\RefundAction;
use Marem\PayumPaybox\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class PayboxGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'paybox',
            'payum.factory_title' => 'Paybox',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'site' => '',
                'rang' => '',
                'identifiant' => '',
                'hmac' => '',
                'hash' => 'SHA512',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('site', 'rang', 'identifiant', 'hmac');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
