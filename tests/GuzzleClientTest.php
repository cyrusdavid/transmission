<?php

use Mockery as m;

class GuzzleClientTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->clientErrorResponseException = m::mock('Guzzle\\Http\\Exception\\ClientErrorResponseException');
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

        $this->assertInstanceOf('Guzzle\\Http\\Client', $client->getVendorClient());
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

        $this->clientErrorResponseException->shouldReceive('getResponse')->andReturn($response);

        $validRes = m::mock();
        $validRes->shouldReceive('getBody')->with(true)->once()->andReturn(json_encode($res));

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('send')->once()->andThrow($this->clientErrorResponseException);
        $client->shouldReceive('send')->once()->andReturn($validRes);
        $client->shouldReceive('setDefaultOption')
               ->with('headers/X-Transmission-Session-Id', 'foo');

        $gclient = new Vohof\GuzzleClient('http://foo');
        $gclient->setVendorClient($client);

        $this->assertSame($res['arguments'], $gclient->request('foo', array()));
    }

    /**
     * @covers Vohof\GuzzleClient::request()
     * @expectedException Guzzle\Http\Exception\ClientErrorResponseException
     */
    public function testRequestNon409StatusCode()
    {
        $response = m::mock();
        $response->shouldReceive('getStatusCode')->andReturn(123456);
        $response->shouldReceive('hasHeader')->with('X-Transmission-Session-Id')->andReturn(false);

        $this->clientErrorResponseException->shouldReceive('getResponse')->andReturn($response);

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('send')->once()->andThrow($this->clientErrorResponseException);

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

        $this->clientErrorResponseException->shouldReceive('getResponse')->andReturn($response);

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('send')->once()->andThrow($this->clientErrorResponseException);

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

        $this->clientErrorResponseException->shouldReceive('getResponse')->andReturn($response);

        $invalidRes = m::mock();
        $invalidRes->shouldReceive('getBody')->with(true)->once()->andReturn('dfdf');

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('send')->once()->andThrow($this->clientErrorResponseException);
        $client->shouldReceive('send')->once()->andReturn($invalidRes);
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

        $this->clientErrorResponseException->shouldReceive('getResponse')->andReturn($response);

        $unsuccessfulRes = m::mock();
        $unsuccessfulRes->shouldReceive('getBody')->with(true)->once()->andReturn(json_encode($res));

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('send')->once()->andThrow($this->clientErrorResponseException);
        $client->shouldReceive('send')->once()->andReturn($unsuccessfulRes);
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

        $this->clientErrorResponseException->shouldReceive('getResponse')->andReturn($response);

        $unsuccessfulRes = m::mock();
        $unsuccessfulRes->shouldReceive('getBody')->with(true)->once()->andReturn(json_encode($res));

        $client = m::mock();
        $client->shouldReceive('post')->andReturn($client);
        $client->shouldReceive('send')->once()->andThrow($this->clientErrorResponseException);
        $client->shouldReceive('send')->once()->andReturn($unsuccessfulRes);
        $client->shouldReceive('setDefaultOption')
               ->with('headers/X-Transmission-Session-Id', 'foo');

        $gclient = new Vohof\GuzzleClient('http://foo');
        $gclient->setVendorClient($client);

        $gclient->request('foo', array());
    }
}
