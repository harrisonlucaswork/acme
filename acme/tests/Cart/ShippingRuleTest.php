<?php

use App\Cart\Cart;
use App\Cart\ShippingRule;
use App\Storage\StorageInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class ShippingRuleTest extends TestCase
{
	public function tearDown() : void
	{
		Mockery::close();
	}

	public function testInvalidRuleData()
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid rule');
		$rule = new ShippingRule([]);
	}

	public function testGetPrice()
	{
		$rule = new ShippingRule(['price' => '1.00', 'conditions' => ['foo' => 'bar']], $this->getMockStorage());
		$this->assertSame('1.00', $rule->getPrice());
	}

	public function testIsSatisfiedBy()
	{
		$storage = $this->getMockStorage();
		$rule = new ShippingRule(['price' => '1.00', 'conditions' => [
				ShippingRule::SUBTOTAL_CONDITION => [
						ShippingRule::GREATER_THAN => '9.98',
						ShippingRule::LESS_THAN => '10.00',
				],
				ShippingRule::ITEM_COUNT_CONDITION => [
						ShippingRule::GREATER_THAN => 1,
						ShippingRule::LESS_THAN => 3,
				],
		]], $storage);
		$rule2 = new ShippingRule(['price' => '1.00', 'conditions' => [
				ShippingRule::SUBTOTAL_CONDITION => [
						ShippingRule::LESS_THAN => '10.00',
				],
				ShippingRule::ITEM_COUNT_CONDITION => [
						ShippingRule::LESS_THAN => 5,
				],
		]], $storage);
		$rule3 = new ShippingRule(['price' => '1.00', 'conditions' => [
				ShippingRule::SUBTOTAL_CONDITION => [
						ShippingRule::GREATER_THAN => '10.00',
				],
				ShippingRule::ITEM_COUNT_CONDITION => [
						ShippingRule::LESS_THAN => 2,
				],
		]], $storage);

		$cart = $this->getMockCart();

		$this->assertTrue($rule->isSatisfiedBy($cart));
		$this->assertTrue($rule2->isSatisfiedBy($cart));
		$this->assertFalse($rule3->isSatisfiedBy($cart));
	}

	public function testWithExampleRedBasketNumbers()
	{
		$cart = Mockery::mock(Cart::class);
		$cart->shouldReceive('getSubtotal')
			->andReturn('65.90');
		$cart->shouldReceive('getDiscountTotal')
			->andReturn('16.48');

		$storage = $this->getMockStorage();
		$rule = new ShippingRule(['price' => '4.95', 'conditions' => [
				ShippingRule::SUBTOTAL_CONDITION => [
						ShippingRule::LESS_THAN => '50.00',
				]
		]], $storage);
		$rule2 = new ShippingRule(['price' => '2.95', 'conditions' => [
				ShippingRule::SUBTOTAL_CONDITION => [
						ShippingRule::GREATER_THAN => '50.00',
						ShippingRule::LESS_THAN => '90.00',
				]
		]], $storage);

		$this->assertTrue($rule->isSatisfiedBy($cart));
		$this->assertFalse($rule2->isSatisfiedBy($cart));
	}

	private function getMockCart() : Cart
	{
		$cart = Mockery::mock(Cart::class);
		$cart->shouldReceive('getSubtotal')
			->andReturn('10.99');
		$cart->shouldReceive('getCount')
			->andReturn(2);
		$cart->shouldReceive('getDiscountTotal')
			->andReturn('1.00');

		return $cart;
	}

	private function getMockStorage() : StorageInterface
	{
		$storage = Mockery::mock(StorageInterface::class);
		$storage->shouldReceive('getCurrencyCode')
			->andReturn('USD');

		return $storage;
	}
}
