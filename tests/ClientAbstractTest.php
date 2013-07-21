<?php

class ClientAbstractTest extends TestCase {

    /**
     * @covers Vohof\ClientAbstract::getVendorClient()
     */
    public function testGetVendorClient()
    {
        $c = new ReflectionClass('TestableClientAbstract');
        $property = $c->getProperty('client');
        $property->setAccessible(true);

        $c2 = new TestableClientAbstract;
        $property->setValue($c2, 'foo');

        $this->assertEquals('foo', $c2->getVendorClient());
    }

    /**
     * @covers Vohof\ClientAbstract::setVendorClient()
     */
    public function testSetVendorClient()
    {
        $c = new ReflectionClass('TestableClientAbstract');
        $property = $c->getProperty('client');
        $property->setAccessible(true);

        $c2 = new TestableClientAbstract;
        $c2->setVendorClient('foo');

        $this->assertEquals('foo', $property->getValue($c2));
    }

    /**
     * @covers Vohof\ClientAbstract::setEndpoint()
     */
    public function testSetEndpoint()
    {
        $c = new ReflectionClass('TestableClientAbstract');
        $property = $c->getProperty('endpoint');
        $property->setAccessible(true);

        $c2 = new TestableClientAbstract;
        $c2->setEndpoint('foo');

        $this->assertEquals('foo', $property->getValue($c2));
    }
}

class TestableClientAbstract extends Vohof\ClientAbstract {

    public function request($method, $params) {}
}
