<?php

use \PHPUnit\Framework\TestCase;
use App\Cart\Cart;
use App\Cart\DiscountRule;
use App\Cart\RuleInterface;
use Tests\Cart\MockLineItem;
use App\Storage\StorageInterface;
use Mockery;

class DiscountRuleTest extends TestCase
{
    public function tearDown() : void
    {
        Mockery::close();
    }

    public function testInvalidRuleData()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid rule');
        $rule = new DiscountRule([]);
    }

    public function testGetPrice50PercentOff()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '10.00'));
        $storage->shouldReceive('getCurrencyCode')
            ->andReturn('USD');

        $rule = new DiscountRule(['price' => '0.5', 'conditions' => [
                DiscountRule::BUY_ONE_GET_SECOND_DISCOUNT => 'mock',
        ]], $storage);

        $this->assertSame('5.00', $rule->getPrice());
    }


    public function testGetPrice75PercentOff()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '10.00'));
        $storage->shouldReceive('getCurrencyCode')
            ->andReturn('USD');

        $rule = new DiscountRule(['price' => '0.75', 'conditions' => [
                DiscountRule::BUY_ONE_GET_SECOND_DISCOUNT => 'mock',
        ]], $storage);

        $this->assertSame('7.50', $rule->getPrice());
    }

    public function testIsSatisfiedByBuyTwoGetThirdDiscountNotEnough()
    {
        $rule = new DiscountRule(['price' => '0.5', 'conditions' => [
                DiscountRule::BUY_TWO_GET_THIRD_DISCOUNT => 'mock',
        ]]);

        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('getLineItemCounts')
            ->andReturn(['mock' => 2]);

        $this->assertFalse($rule->isSatisfiedBy($cart));
    }

    public function testIsSatisfiedByBuyTwoGetThirdDiscount()
    {
        $rule = new DiscountRule(['price' => '0.5', 'conditions' => [
                DiscountRule::BUY_TWO_GET_THIRD_DISCOUNT => 'mock',
        ]]);

        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('getLineItemCounts')
            ->andReturn(['mock' => 3]);

        $this->assertTrue($rule->isSatisfiedBy($cart));
    }

    public function testIsSatisfiedByBuyOneGetSecondDiscount()
    {
        $rule = new DiscountRule(['price' => '0.5', 'conditions' => [
                DiscountRule::BUY_ONE_GET_SECOND_DISCOUNT => 'mock',
        ]]);

        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('getLineItemCounts')
            ->andReturn(['mock' => 2]);

        $this->assertTrue($rule->isSatisfiedBy($cart));
    }

    public function testBuyOneGetOne()
    {
        $storage = Mockery::mock(StorageInterface::class);
        $storage->shouldReceive('getProduct')
            ->with('mock')
            ->andReturn(new MockLineItem('mock', '10.00'));
        $storage->shouldReceive('getCurrencyCode')
            ->andReturn('USD');
        $rule = new DiscountRule(['price' => '1.0', 'conditions' => [
                DiscountRule::BUY_ONE_GET_SECOND_DISCOUNT => 'mock',
        ]], $storage);

        $cart = Mockery::mock(Cart::class);
        $cart->shouldReceive('getLineItemCounts')
            ->andReturn(['mock' => 2]);

        $this->assertTrue($rule->isSatisfiedBy($cart));
        $this->assertSame('10.00', $rule->getPrice());
    }
}
