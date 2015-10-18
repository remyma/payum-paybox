<?php

/*
 * This file is part of the PayumPaybox package.
 *
 * (c) Matthieu REMY
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Marem\PayumPaybox\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryInterface;

class CaptureAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $this->gateway->execute(new Payment($model));
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