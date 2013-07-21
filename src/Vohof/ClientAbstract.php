<?php namespace Vohof;

abstract class ClientAbstract {

    protected $client;

    protected $connected = false;

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

    protected function isConnected()
    {
        return $this->connected;
    }
}
