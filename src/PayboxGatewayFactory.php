<?php

/*
 * This file is part of the PayumPaybox package.
 *
 * (c) Matthieu REMY
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Marem\PayumPaybox;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use marem\PayumPaybox\Action\CaptureAction;
use Marem\PayumPaybox\Api;

class PayboxPaymentFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(array(
            'payum.factory_name' => 'paybox',
            'payum.factory_title' => 'Paybox',

            'payum.action.capture' => new CaptureAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'site' => '',
                'rang' => '',
                'identifiant' => '',
                'hmac' => '',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('username', 'password', 'signature');
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);
                $payboxConfig = array(
                    'site' => $config['site'],
                    'rang' => $config['rang'],
                    'identifiant' => $config['identifiant'],
                    'hmac' => $config['hmac'],
                    'sandbox' => $config['sandbox'],
                );
                return new Api($payboxConfig, $config['payum.http_client']);
            };
        }
    }
}