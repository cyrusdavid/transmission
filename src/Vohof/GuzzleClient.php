<?php namespace Vohof;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class GuzzleClient extends ClientAbstract
{

    protected $lastRequest;

    protected $retries = 0;

    protected $maxRetries = 5;


    public function __construct($host, $options = [ ])
    {
        $options['base_uri'] = $host;
        $this->setVendorClient(new Client($options));
    }


    public function request($method, $params = [ ])
    {
        $this->lastRequest = func_get_args();
        try {
            $res = $this->client->post($method, $params)->json();

            if (!is_array($res)) {
                throw new TransmissionBadJsonException('The response from RPC server is invalid.');
            }

            if ($res['result'] != 'success') {
                throw new TransmissionResponseException("The RPC server did not return a success result flag: ${res['result']}");
            }

            if ( ! isset( $res['arguments'] )) {
                throw new TransmissionResponseException("The RPC server did not return any arguments.");
            }

            return $res['arguments'];
        } catch (ClientException $e) {
            $response  = $e->getResponse();
            $errorCode = $response->getStatusCode();

            if ($errorCode == 409) {
                if ( ! $response->hasHeader('X-Transmission-Session-Id')) {
                    throw new TransmissionSessionException('No X-Transmission-Session-Id header found.');
                }

                $sessionId = $response->getHeader('X-Transmission-Session-Id');

                $this->client->setDefaultOption('headers/X-Transmission-Session-Id', $sessionId);

                $this->retries++;

                if ($this->retries > $this->maxRetries) {
                    throw new TransmissionSessionException('Transmission doesn\'t like our session Id.');
                }

                return call_user_func_array([ $this, 'request' ], $this->lastRequest);
            }

            throw $e;
        }
    }
}

class TransmissionBadJsonException extends \Exception
{

}

class TransmissionResponseException extends \Exception
{

}

class TransmissionSessionException extends \Exception
{

}
