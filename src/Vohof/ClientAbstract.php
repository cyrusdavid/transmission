<?php namespace Vohof;

abstract class ClientAbstract {

    protected $client;

    protected $endpoint;

    abstract public function request($method, $params);

    public function getVendorClient()
    {
        return $this->client;
    }

    public function setVendorClient($client)
    {
        $this->client = $client;
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }
}
