<?php

namespace Marem\PayumPaybox\Action;


use Marem\PayumPaybox\Api;
use Marem\PayumPaybox\Request\Api\ChoosePaymentType;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;

class ChoosePaymentTypeAction extends GatewayAwareAction implements ApiAwareInterface
{
    use ApiAwareTrait;

    /**
    * @var string
    */
    protected $templateName;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->apiClass = Api::class;
        $this->templateName = $templateName;
    }

    /**
     * {@inheritDoc}
     *
     * @param ChoosePaymentType $request
     */
    public function execute($request)
    {

        RequestNotSupportedException::assertSupports($this, $request);


        $details = ArrayObject::ensureArrayObject($request->getModel());

        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);
        if ($getHttpRequest->method == 'POST' && isset($getHttpRequest->request['paymentType'])) {
            $details['PBX_TYPEPAIEMENT'] = $getHttpRequest->request['paymentType'];
            $details['PBX_TYPECARTE'] = $getHttpRequest->request['cardType'];
            return null;
        }

        $template = new RenderTemplate($this->templateName, array(
            'model' => $details,
            'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
        ));

        $this->gateway->execute($template);

        throw new HttpResponse($template->getResult());
    }

    /**
    * {@inheritDoc}
    */
    public function supports($request)
    {
        return
            $request instanceof ChoosePaymentType &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }

}