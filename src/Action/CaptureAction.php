<?php
namespace Marem\PayumPaybox\Action;

use Marem\PayumPaybox\Action\Api\BaseApiAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;

class CaptureAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if (isset($httpRequest->query['error_code'])) {
            $details->replace($httpRequest->query);
        } else {
            $response = $this->api->doPayment((array)$details);
            if ($response instanceof HttpPostRedirect) {
                echo $response->getContent();die;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
