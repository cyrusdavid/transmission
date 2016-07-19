<?php

use Mockery as m;

class GuzzleClientTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->ClientException = m::mock('GuzzleHttp\\Exception\\ClientException');
    }

    public function tearDown()
    {
        m::close();
    }

    /**
     * @covers Vohof\GuzzleClient::__construct()
     */
    public function test__construct()
    {
        $client = new Vohof\GuzzleClient('http://foo');

        $this->assertInstanceOf('GuzzleHttp\\Client', $client->getVendorClient());
    }

    /**
     * @covers Vohof\GuzzleClient::request()
     */
    public function testRequestSuccess()
    {
        $res = array(
            'result' => 'success',
            'arguments' => array(
                'torrent-added' => array(
                    'hashString' => 'foo',
                    'id' => 1,
                    'name' => 'bar'
                )
            )
        );

        $response = m::mock();
        $response->shouldReceive('getStatusCode')->andReturn(409);
        $response->shouldReceive('getHeader')->once()->with('X-Transmission-Session-Id')->andReturn('foo');
        $response->shouldReceive('hasHeader')->with('X-Transmission-Session-Id')->andReturn(true);

        $this->ClientException->shouldReceive('getResponse')->andReturn($response);

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('getBody')->once()->andThrow($this->ClientException);
        $client->shouldReceive('getBody')->once()->andReturn(json_encode($res));
        $client->shouldReceive('setDefaultOption')
            ->with('headers/X-Transmission-Session-Id', 'foo');

        $gclient = new Vohof\GuzzleClient('http://foo');
        $gclient->setVendorClient($client);

        $this->assertSame($res['arguments'], $gclient->request('foo', array()));
    }

    /**
     * @covers Vohof\GuzzleClient::request()
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function testRequestNon409StatusCode()
    {
        $response = m::mock();
        $response->shouldReceive('getStatusCode')->andReturn(123456);
        $response->shouldReceive('hasHeader')->with('X-Transmission-Session-Id')->andReturn(false);

        $this->ClientException->shouldReceive('getResponse')->andReturn($response);

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('getBody')->once()->andThrow($this->ClientException);

        $gclient = new Vohof\GuzzleClient('http://foo');
        $gclient->setVendorClient($client);

        $gclient->request('foo', array());
    }

    /**
     * @covers Vohof\GuzzleClient::request()
     * @expectedException Vohof\TransmissionSessionException
     * @expectedExceptionMessage No X-Transmission-Session-Id header found.
     */
    public function testRequestNoHeader()
    {
        $response = m::mock();
        $response->shouldReceive('getStatusCode')->andReturn(409);
        $response->shouldReceive('hasHeader')->with('X-Transmission-Session-Id')->andReturn(false);

        $this->ClientException->shouldReceive('getResponse')->andReturn($response);

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('getBody')->once()->andThrow($this->ClientException);

        $gclient = new Vohof\GuzzleClient('http://foo');
        $gclient->setVendorClient($client);

        $gclient->request('foo', array());
    }

    /**
     * @covers Vohof\GuzzleClient::request()
     * @expectedException Vohof\TransmissionBadJsonException
     * @expectedExceptionMessage The response from RPC server is invalid.
     */
    public function testRequestMisformatedJson()
    {
        $response = m::mock();
        $response->shouldReceive('getStatusCode')->andReturn(409);
        $response->shouldReceive('getHeader')->once()->with('X-Transmission-Session-Id')->andReturn('foo');
        $response->shouldReceive('hasHeader')->with('X-Transmission-Session-Id')->andReturn(true);

        $this->ClientException->shouldReceive('getResponse')->andReturn($response);

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('getBody')->once()->andThrow($this->ClientException);
        $client->shouldReceive('getBody')->once()->andReturn('dfdf');
        $client->shouldReceive('setDefaultOption')
            ->with('headers/X-Transmission-Session-Id', 'foo');

        $gclient = new Vohof\GuzzleClient('http://foo');
        $gclient->setVendorClient($client);

        $gclient->request('foo', array());
    }

    /**
     * @covers Vohof\GuzzleClient::request()
     * @expectedException Vohof\TransmissionResponseException
     * @expectedExceptionMessage The RPC server did not return a success result flag: not-valid-flag
     */
    public function testRequestNonSuccessResultFlag()
    {
        $res = array(
            'result' => 'not-valid-flag',
            'arguments' => array(
                'torrent-added' => array(
                    'hashString' => 'foo',
                    'id' => 1,
                    'name' => 'bar'
                )
            )
        );

        $response = m::mock();
        $response->shouldReceive('getStatusCode')->andReturn(409);
        $response->shouldReceive('getHeader')->once()->with('X-Transmission-Session-Id')->andReturn('foo');
        $response->shouldReceive('hasHeader')->with('X-Transmission-Session-Id')->andReturn(true);

        $this->ClientException->shouldReceive('getResponse')->andReturn($response);

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('getBody')->once()->andThrow($this->ClientException);
        $client->shouldReceive('getBody')->once()->andReturn(json_encode($res));
        $client->shouldReceive('setDefaultOption')
            ->with('headers/X-Transmission-Session-Id', 'foo');

        $gclient = new Vohof\GuzzleClient('http://foo');
        $gclient->setVendorClient($client);

        $gclient->request('foo', array());
    }

    /**
     * @covers Vohof\GuzzleClient::request()
     * @expectedException Vohof\TransmissionResponseException
     * @expectedExceptionMessage The RPC server did not return any arguments.
     */
    public function testRequestMissingArguments()
    {
        $res = array(
            'result' => 'success'
        );

        $response = m::mock();
        $response->shouldReceive('getStatusCode')->andReturn(409);
        $response->shouldReceive('getHeader')->once()->with('X-Transmission-Session-Id')->andReturn('foo');
        $response->shouldReceive('hasHeader')->with('X-Transmission-Session-Id')->andReturn(true);

        $this->ClientException->shouldReceive('getResponse')->andReturn($response);

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('getBody')->once()->andThrow($this->ClientException);
        $client->shouldReceive('getBody')->once()->andReturn(json_encode($res));
        $client->shouldReceive('setDefaultOption')
            ->with('headers/X-Transmission-Session-Id', 'foo');

        $gclient = new Vohof\GuzzleClient('http://foo');
        $gclient->setVendorClient($client);

        $gclient->request('foo', array());
    }
}
