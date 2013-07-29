<?php namespace Vohof;

class GuzzleException extends \Guzzle\Http\Exception\BadResponseException {

    public static function forge($message)
    {
        $this->message = $message;
    }

    public static function factory(RequestInterface $request, Response $response)
    {
        $e = new $class($this->message);
        $e->setResponse($response);
        $e->setRequest($request);

        return $e;
    }
}
