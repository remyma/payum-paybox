# Payum paybox documentation 

## Gateways

### Paybox System Gateway

#### Configuration

Key | Required | Description | Default value
------------ | ------------- | ------------- | -------------
site | X | site number (provided by paybox) |
rang | X | rang number (provided by paybox) |
identifiant | X | paybox customer id (provided by paybox) |
hmac | X | signin secret key |
hash | | Algorythm used to sign key | sha512
retour | | List of vars paybox shouled return | Mt:M;Ref:R;Auto:A;error_code:E
sandbox | | set test mode | true

## Actions

### Status

### ConvertPayment

### Capture

### ChoosePaymentType

### CancelAction

Todo : Not yet implemented

### NotifyAction

Todo : Not yet implemented

### RefundAction

Todo : Not yet implemented
