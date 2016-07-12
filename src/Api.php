<?php
namespace Marem\PayumPaybox;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Reply\HttpRedirect;
use RuntimeException;

class Api
{
    /**
     * Primary server.
     */
    const MAIN_SERVER = "tpeweb.paybox.com";

    /**
     * Backup server.
     */
    const BACKUP_SERVER = "tpeweb1.paybox.com";

    /**
     * Sandbox server.
     */
    const SANDBOX_SERVER = "preprod-tpeweb.paybox.com";

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }


    public function doPayment(array $fields)
    {
        $fields[PayBoxRequestParams::PBX_SITE] = $this->options['site'];
        $fields[PayBoxRequestParams::PBX_RANG] = $this->options['rang'];
        $fields[PayBoxRequestParams::PBX_IDENTIFIANT] = $this->options['identifiant'];
        $fields[PayBoxRequestParams::PBX_HASH] = $this->options['hash'];
        $fields[PayBoxRequestParams::PBX_RETOUR] = $this->options['retour'];
        $fields[PayBoxRequestParams::PBX_TYPEPAIEMENT] = $this->options['type_paiement'];
        $fields[PayBoxRequestParams::PBX_TYPECARTE] = $this->options['type_carte'];
        $fields[PayBoxRequestParams::PBX_HMAC] = strtoupper($this->computeHmac($this->options['hmac'], $fields));

        $authorizeTokenUrl = $this->getAuthorizeTokenUrl();
        throw new HttpPostRedirect($authorizeTokenUrl, $fields);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest($method, array $fields)
    {
        $headers = [];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    /**
     * Get api end point.
     * @return string server url
     * @throws RuntimeException if no server available
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
     * @return string
     */
    public function getAuthorizeTokenUrl()
    {
        return sprintf(
            'https://%s/cgi/MYchoix_pagepaiement.cgi',
            $this->getApiEndpoint()
        );
    }

    /**
     * @param $hmac string hmac key
     * @param $fields array fields
     * @return string
     */
    protected function computeHmac($hmac, $fields)
    {
        // Si la clé est en ASCII, On la transforme en binaire
        $binKey = pack("H*", $hmac);
        $msg = self::stringify($fields);

        return strtoupper(hash_hmac($fields[PayBoxRequestParams::PBX_HASH], $msg, $binKey));
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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

}
