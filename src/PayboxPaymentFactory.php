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

use marem\PayumPaybox\Action\CaptureAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactoryInterface;

class PayboxPaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $corePaymentFactory;

    /**
     * @var array
     */
    private $defaultConfig;

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->corePaymentFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->corePaymentFactory->createConfig((array) $config));

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

        return (array) $config;
    }
}