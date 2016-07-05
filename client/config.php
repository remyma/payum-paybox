<?php
//config.php

require __DIR__ . '/../vendor/autoload.php';

use Payum\Core\GatewayFactoryInterface;
use Payum\Core\PayumBuilder;
use Payum\Core\Payum;
use Payum\Core\Model\Payment;

$paymentClass = Payment::class;

$config = array(
    'site' => '1999888',
    'rang' => '32',
    'identifiant' => '3',
    'hmac' => '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF',
    'sandbox' => true,
);

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGatewayFactory('paybox', function(array $config, GatewayFactoryInterface $coreGatewayFactory) use ($config) {
        return new \Marem\PayumPaybox\PayboxGatewayFactory($config, $coreGatewayFactory);
    })
    ->addGateway('paybox', [
        'factory' => 'paybox'
    ])
    ->getPayum()
;