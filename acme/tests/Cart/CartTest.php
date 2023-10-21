<?php

use PHPUnit\Framework\TestCase;
use App\Cart\Cart;
use App\Cart\RuleInterface;
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

    public function testGetShippingTotal()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '1.00'));
        $storage->shouldReceive('getCurrencyCode')
            ->andReturn('USD');
        $storage->shouldReceive('getShippingRules')
            ->andReturn($this->getMockShippingRules());

        $cart = new Cart($storage);
        $cart->add('mock');

        $this->assertSame('4.99', $cart->getShippingTotal());
    }

    public function testGetDiscountTotal()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '1.00'));
        $storage->shouldReceive('getCurrencyCode')
            ->andReturn('USD');
        $storage->shouldReceive('getDiscountRules')
            ->andReturn($this->getMockDiscountRules());

        $cart = new Cart($storage);
        $cart->add('mock');

        $this->assertSame('1.00', $cart->getDiscountTotal());
    }

    public function testGetTotal()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '1.00'));
        $storage->shouldReceive('getCurrencyCode')
            ->andReturn('USD');
        $storage->shouldReceive('getShippingRules')
            ->andReturn($this->getMockShippingRules());
        $storage->shouldReceive('getDiscountRules')
            ->andReturn($this->getMockDiscountRules());

        $cart = new Cart($storage);
        $cart->add('mock');

        $this->assertSame('4.99', $cart->getTotal());
    }

    public function testGetLineItemCounts()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '1.00'));
        $storage->shouldReceive('getProduct')
            ->with('mock2')
            ->andReturn(new MockLineItem('mock2', '1.00'));
        $storage->shouldReceive('getCurrencyCode')
            ->andReturn('USD');

        $cart = new Cart($storage);
        $cart->add('mock');
        $cart->add('mock');
        $cart->add('mock2');

        $this->assertSame(['mock' => 2, 'mock2' => 1], $cart->getLineItemCounts());
    }

    private function getMockShippingRules() : array
    {
        $shippingRule = Mockery::mock(RuleInterface::class);
        $shippingRule->shouldReceive('isSatisfiedBy')
            ->andReturn(false);
        $shippingRule->shouldReceive('getPrice')
            ->never();
        $shippingRule2 = Mockery::mock(RuleInterface::class);
        $shippingRule2->shouldReceive('isSatisfiedBy')
            ->andReturn(true);
        $shippingRule2->shouldReceive('getPrice')
            ->andReturn('5.00');
        $shippingRule3 = Mockery::mock(RuleInterface::class);
        $shippingRule3->shouldReceive('isSatisfiedBy')
            ->andReturn(true);
        $shippingRule3->shouldReceive('getPrice')
            ->andReturn('4.99');
        return [
            $shippingRule,
            $shippingRule2,
            $shippingRule3,
        ];
    }

    private function getMockDiscountRules() : array
    {
        $discountRule = Mockery::mock(RuleInterface::class);
        $discountRule->shouldReceive('isSatisfiedBy')
            ->andReturn(false);
        $discountRule->shouldReceive('getPrice')
            ->never();
        $discountRule2 = Mockery::mock(RuleInterface::class);
        $discountRule2->shouldReceive('isSatisfiedBy')
            ->andReturn(true);
        $discountRule2->shouldReceive('getPrice')
            ->andReturn('1.00');
        $discountRule3 = Mockery::mock(RuleInterface::class);
        $discountRule3->shouldReceive('isSatisfiedBy')
            ->andReturn(true);
        $discountRule3->shouldReceive('getPrice')
            ->andReturn('0.99');

        return [
            $discountRule,
            $discountRule2,
            $discountRule3,
        ];
    }
}
