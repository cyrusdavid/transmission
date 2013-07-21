<?php

use Mockery as m;

class TransmissionTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->config = array(
            'endpoint' => '/transmission/rpc',
            'host' => 'http://foo'
        );

        $this->client = new Vohof\GuzzleClient('http://foo');
        $this->clientMock = m::mock($this->client);
        $this->tr = new Vohof\Transmission($this->config, $this->clientMock);
    }

    /**
     * @covers Vohof\Transmission::__construct()
     */
    public function test__construct()
    {
        $mock = m::mock($this->client);
        $mock->shouldReceive('setEndpoint')->once()->with($this->config['endpoint']);

        $m = new Vohof\Transmission($this->config, $mock);

        $this->assertSame($this->config, $m->getconfig());
        $this->assertInstanceOf('Vohof\\ClientAbstract', $m->getClient());
    }

    /**
     * @covers Vohof\Transmission::__construct()
     */
    public function test__constructNoClient()
    {
        $m = new Vohof\Transmission($this->config);

        $this->assertSame($this->config, $m->getconfig());
        $this->assertInstanceOf('Vohof\\ClientAbstract', $m->getClient());
    }
    /**
     * @covers Vohof\Transmission::__construct()
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing argument: host
     */
    public function test__constructMissingHost()
    {
        unset($this->config['host']);

        $m = new Vohof\Transmission($this->config);
    }
    /**
     * @covers Vohof\Transmission::__construct()
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Missing argument: endpoint
     */
    public function test__constructMissingEndpoint()
    {
        unset($this->config['endpoint']);

        $m = new Vohof\Transmission($this->config);
    }

    /**
     * @covers Vohof\Transmission::getConfig()
     */
    public function testGetConfig()
    {
        $m = new Vohof\Transmission($this->config, $this->client);

        $this->assertSame($this->config, $m->getconfig());
    }

    /**
     * @covers Vohof\Transmission::getClient()
     */
    public function testGetClient()
    {
        $m = new Vohof\Transmission($this->config, $this->client);

        $this->assertInstanceOf('Vohof\\ClientAbstract', $m->getClient());
    }

    /**
     * @covers VOhof\Transmission::add()
     */
    public function testAdd()
    {
        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('torrent-add', array('filename' => 'foo'));

        $this->tr->add('foo');

        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('torrent-add', array('filename' => 'foo', 'paused' => true));

        $this->tr->add('foo', false, array('paused' => true));
    }

    /**
     * @covers VOhof\Transmission::add()
     */
    public function testAddBase64Encoded()
    {
        $val = base64_encode('foo');

        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('torrent-add', array('metainfo' => $val));

        $this->tr->add($val, true);

        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('torrent-add', array('metainfo' => $val, 'paused' => true));

        $this->tr->add($val, true, array('paused' => true));
    }

    /**
     * @covers Vohof\Transmission::action()
     */
    public function testAction()
    {
        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('torrent-start', array('ids' => array(1)));

        $this->tr->action('start', 1);
    }

    /**
     * @covers Vohof\Transmission::set()
     */
    public function testSet()
    {
        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('torrent-set', array('ids' => array(1), 'downloadLimit' => 50));

        $this->tr->set(1, array('downloadLimit' => 50));
    }

    /**
     * @covers Vohof\Transmission::get()
     */
    public function testGet()
    {
        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('torrent-get', array('fields' => array('downloadLimit')));

        $this->tr->get('all', array('downloadLimit'));

        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('torrent-get', array('ids' => array(1), 'fields' => array('downloadLimit')));

        $this->tr->get(1, array('downloadLimit'));
    }

    /**
     * @covers Vohof\Transmission::remove()
     */
    public function testRemove()
    {
        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('torrent-remove', array('ids' => array(1), 'delete-local-data' => false));

        $this->tr->remove(1);

        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('torrent-remove', array('ids' => array(1), 'delete-local-data' => true));

        $this->tr->remove(1, true);
    }

    /**
     * @covers Vohof\Transmission::session()
     */
    public function testSession()
    {
        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('session-get');

        $this->tr->session();

        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('session-set', array('foo' => 'bar'));

        $this->tr->session('foo', 'bar');
    }

    /**
     * @covers Vohof\Transmission::stats()
     */
    public function testStats()
    {
        $this->clientMock->shouldReceive('request')
          ->once()
          ->with('session-stats');

        $this->tr->getStats();
    }

    /**
     * @covers Vohof\Transmission::relocate()
     */
    public function testRelocate()
    {
        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('torrent-set-location',
                   array('ids' => array(1), 'location' => '/foo', 'move' => false));

        $this->tr->relocate(1, '/foo');

        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('torrent-set-location',
                   array('ids' => array(1), 'location' => '/foo', 'move' => true));

        $this->tr->relocate(1, '/foo', true);
    }

    /**
     * @covers Vohof\Transmission::port()
     */
    public function testPort()
    {
        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('port-test');

        $this->tr->isPeerPortOpen();
    }

    /**
     * @covers Vohof\Transmission::close()
     */
    public function testClose()
    {
        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('session-close');

        $this->tr->close();
    }

    /**
     * @covers Vohof\Transmission::done()
     */
    public function testDone()
    {
        $tr = m::mock('Vohof\\Transmission[close]', array($this->config, $this->clientMock));
        $tr->shouldReceive('close')->once();

        $tr->done();
    }

    /**
     * @covers Vohof\Transmission::queue()
     */
    public function testQueue()
    {
        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('queue-move-up', array('ids' => array(1)));

        $this->tr->queue(1, 'up');
    }

    /**
     * @covers Vohof\Transmission::queue()
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid queue direction: north-west
     */
    public function testQueueNorthWest()
    {
        $this->tr->queue(1, 'north-west');
    }

    /**
     * @covers Vohof\Transmission::getFreeSpace()
     */
    public function testGetFreeSpace()
    {
        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('free-space', array('path' => '/'));

        $this->tr->getFreeSpace();

        $this->clientMock->shouldReceive('request')
            ->once()
            ->with('free-space', array('path' => '/home'));

        $this->tr->getFreeSpace('/home');
    }
}
