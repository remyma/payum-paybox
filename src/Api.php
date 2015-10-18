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

use GuzzleHttp\Psr7\Request;
use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\LogicException;
use Payum\Core\HttpClientInterface;

class Api
{

    /**
     * @var HttpClientInterface
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
     * @param HttpClientInterface $client
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client = null)
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
        $this->client = $client ?: HttpClientFactory::create();
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    public function payment(array $fields)
    {
        var_dump($fields);die;
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest(array $fields)
    {
        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );
        $request = new Request('POST', $this->getApiEndpoint(), $headers, http_build_query($fields));
        $response = $this->client->send($request);
        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }
        $result = array();
        parse_str($response->getBody()->getContents(), $result);
        foreach ($result as &$value) {
            $value = urldecode($value);
        }
        return $result;
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