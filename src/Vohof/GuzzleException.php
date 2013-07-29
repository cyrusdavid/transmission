<?php namespace Vohof;

use Guzzle\Http\Message\RequestInterface;
use Guzzle\Http\Message\Response;

class GuzzleException extends \Guzzle\Http\Exception\BadResponseException {

    public static function forge($message, RequestInterface $request, Response $response)
    {
        $e = new static($message);
        $e->setResponse($response);
        $e->setRequest($request);

        return $e;
    }
}
