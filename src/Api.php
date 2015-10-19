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

use Buzz\Client\ClientInterface;
use Buzz\Message\Form\FormRequest;
use Payum\Core\Bridge\Buzz\ClientFactory;
use Payum\Core\Bridge\Buzz\JsonResponse;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\LogicException;

class Api
{

    /**
     * @var \Buzz\Client\ClientInterface
     */
    protected $client;

    /**
     * @var array
     */
    protected $options = array(
        'site' => null,
        'rang' => null,
        'identifiant' => null,
        'hmac' => null,
        'sandbox' => null,
    );

    /**
     * @param array               $options
     * @param ClientInterface $client
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, ClientInterface $client = null)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty(array(
            'site',
            'rang',
            'identifiant',
            'hmac'
        ));

        if (false == is_bool($options['sandbox'])) {
            throw new LogicException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client = ClientFactory::createCurl();
    }

    /**
     * @param array $fields
     *
     * @return \Payum\Core\Bridge\Buzz\JsonResponse
     */
    public function payment(array $fields)
    {
        var_dump($fields);die;
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * @param \Buzz\Message\Form\FormRequest $request
     *
     * @throws \Payum\Core\Exception\Http\HttpException
     *
     * @return \Payum\Core\Bridge\Buzz\JsonResponse
     */
    protected function doRequest(FormRequest $request)
    {
        $request->setMethod('POST');
        $request->fromUrl($this->getApiEndpoint());
        $this->client->send($request, $response = new JsonResponse());
        if (false == $response->isSuccessful()) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }


    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://preprod-tpeweb.paybox.com' :
            'https://tpeweb.paybox.com'
            ;
    }

    /**
     * @param array $fields
     */
    protected function addAuthorizeFields(array &$fields)
    {
        $fields['PBX_SITE'] = $this->options['site'];
        $fields['PBX_RANG'] = $this->options['rang'];
        $fields['PBX_IDENTIFIANT'] = $this->options['identifiant'];
        $fields['PBX_HMAC'] = $this->options['hmac'];
    }
}