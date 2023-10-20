<?php

use PHPUnit\Framework\TestCase;
use App\Cart\Cart;
use Tests\Cart\MockLineItem;
use App\Storage\StorageInterface;
use Mockery;

class CartTest extends TestCase
{
    public function tearDown() : void
    {
        Mockery::close();
    }

    public function testAdd()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '1.00'));
        $cart = new Cart($storage);
        $cart->add('mock');

        $this->assertSame(1, $cart->getCount());
    }

    public function testAddWithInvalidProduct()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(null);
        $cart = new Cart($storage);
        $cart->add('mock');

        $this->assertSame(0, $cart->getCount());
    }

    public function testAddWithMultipleSameProducts()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '1.00'));
        $cart = new Cart($storage);
        $cart->add('mock');
        $cart->add('mock');

        $this->assertSame(2, $cart->getCount());
    }

    public function testGetSubtotal()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '1.01'));
        $storage->shouldReceive('getCurrencyCode')
            ->andReturn('USD');
        $cart = new Cart($storage);
        $cart->add('mock');
        $cart->add('mock');

        $this->assertSame('2.02', $cart->getSubtotal());
    }

    /**
     * Money should throw an exception if we have bad product data that goes beyond the
     * given precision of a currency. Ie past the 2 decimal places for USD.
     * @expectedException \Brick\Money\Exception\RoundingNecessaryException
     */
    public function testGetSubtotalThrowsRoundingException()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '1.01'));
        $storage->shouldReceive('getProduct')
            ->with('mock2')
            ->andReturn(new MockLineItem('mock2', '1.001'));
        $storage->shouldReceive('getCurrencyCode')
            ->andReturn('USD');

        $cart = new Cart($storage);
        $cart->add('mock');
        $cart->add('mock2');

        $this->expectException(\Brick\Math\Exception\RoundingNecessaryException::class);
        $cart->getSubtotal();
    }
}
