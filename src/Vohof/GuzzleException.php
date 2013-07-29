<?php namespace Vohof;

class GuzzleException extends \Guzzle\Http\Exception\BadResponseException {

    public static function factory($message, RequestInterface $request, Response $response)
    {
        $e = new $class($message);
        $e->setResponse($response);
        $e->setRequest($request);

        return $e;
    }
}
