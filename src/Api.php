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

use Buzz\Message\Response;
use Buzz\Message\Form\FormRequest;
use Payum\Core\Bridge\Buzz\ClientFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\HttpRedirect;
use RuntimeException;

class Api
{
    const MAIN_SERVER = "tpeweb.paybox.com";

    const BACKUP_SERVER = "tpeweb1.paybox.com";

    const SANDBOX_SERVER = "preprod-tpeweb.paybox.com";

    const PBX_HASH = "SHA512";

    /**
     * @var \Buzz\Client\Curl
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
        $client = ClientFactory::createCurl();
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
        $this->client = $client;
    }

    /**
     * @param array $fields
     *
     * @return \Payum\Core\Bridge\Buzz\JsonResponse
     */
    public function payment(array $fields)
    {
        $request = new FormRequest();

        $fields['PBX_SITE'] = $this->options['site'];
        $fields['PBX_RANG'] = $this->options['rang'];
        $fields['PBX_IDENTIFIANT'] = $this->options['identifiant'];
        $fields['PBX_HMAC'] = strtoupper($this->computeHmac($this->options['hmac'], $fields));

        $request->setField('params', $fields);

        throw new HttpRedirect(
            $this->getAuthorizeTokenUrl($fields)
        );
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
        $request->setMethod('GET');
        $request->fromUrl($this->getApiEndpoint());

        $this->client->send($request, $response = new Response());

        if (false == $response->isSuccessful()) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    /**
     * @param array  $fields
     *
     * @return string
     */
    public function getAuthorizeTokenUrl(array $fields = array())
    {
        $query = array_filter($fields);

        return sprintf(
            'https://%s/cgi/MYchoix_pagepaiement.cgi?%s',
            $this->getApiEndpoint(),
            http_build_query($query)
        );
    }


    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        $servers = array();
        if ($this->options['sandbox']) {
            $servers[] = self::SANDBOX_SERVER;
        } else {
            $servers = array(self::MAIN_SERVER, self::BACKUP_SERVER);
        }

        foreach ($servers as $server) {
            $doc = new \DOMDocument();
            $doc->loadHTMLFile('https://'. $server . "/load.html");

            $element = $doc->getElementById('server_status');
            if ($element && 'OK' == $element->textContent) {
                return $server;
            }
        }

        throw new RuntimeException('No server available.');
    }

    /**
     * Computes the hmac hash.
     *
     * @param string hmac
     * @param array fields
     * @return string
     */
    protected function computeHmac($hmac, $fields)
    {
        // Si la clé est en ASCII, On la transforme en binaire
        $binKey = pack("H*", $hmac);
        $msg = self::stringify($fields);

        return strtoupper(hash_hmac('sha512', $msg, $binKey));
    }

    /**
     * Makes an array of parameters become a querystring like string.
     *
     * @param  array $array
     *
     * @return string
     */
    static public function stringify(array $array)
    {
        $result = array();
        foreach ($array as $key => $value) {
            $result[] = sprintf('%s=%s', $key, $value);
        }
        return implode('&', $result);
    }

    /**
     * Returns the content of a web resource.
     *
     * @param  string $url
     *
     * @return string
     */
    protected function getWebPage($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,            $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER,         false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $output = curl_exec($curl);
        curl_close($curl);
        return (string) $output;
    }
}