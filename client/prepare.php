<?php
// prepare.php

include 'config.php';

$gatewayName = 'paybox';

$storage = $payum->getStorage($paymentClass);

$payment = $storage->create();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(123); // 1.23 EUR
$payment->setDescription('A description');
$payment->setClientId('anId');
$payment->setClientEmail('foo@example.com');

/* Options */
$payment->setDetails(array(
    'PBX_TYPEPAIEMENT' => 'CARTE',
    'PBX_TYPECARTE' => 'VISA',
    'PBX_RETOUR' => 'Mt:M;Ref:R;Auto:A;error_code:E'
));


$storage->update($payment);

$captureToken = $payum->getTokenFactory()->createCaptureToken($gatewayName, $payment, 'done.php');

header("Location: ".$captureToken->getTargetUrl());