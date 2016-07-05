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

$payment->setDetails(array(
    // put here any fields in a gateway format.
    // for example if you use Paypal ExpressCheckout you can define a description of the first item:
    // 'L_PAYMENTREQUEST_0_DESC0' => 'A desc',
));


$storage->update($payment);

$captureToken = $payum->getTokenFactory()->createCaptureToken($gatewayName, $payment, 'done.php');

header("Location: ".$captureToken->getTargetUrl());