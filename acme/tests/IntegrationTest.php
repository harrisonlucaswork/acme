<?php

use App\Cart\Cart;
use App\Cart\DiscountRule;
use App\Cart\Product;
use App\Cart\ShippingRule;
use App\Storage\Storage;
use Mockery;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
	private array $validInput;
	private Storage $storage;

	public function setUp() : void
	{
		$this->validInput = json_decode(file_get_contents(__DIR__ . '/ValidInput.json'), TRUE);
		$this->storage = new Storage();

		foreach ($this->validInput['Products'] as $product) {
			$this->storage->addProduct(new Product($product['code'], $product['description'], $product['price']));
		}

		foreach ($this->validInput['Shipping'] as $shippingRule) {
			$this->storage->addShippingRule(new ShippingRule($shippingRule));
		}

		foreach ($this->validInput['Discounts'] as $discountRule) {
			$this->storage->addDiscountRule(new DiscountRule($discountRule, $this->storage));
		}
	}

	public function tearDown() : void
	{
		Mockery::close();
	}

	public function testBlueAndGreenWidget()
	{
		$cart = new Cart($this->storage);
		$cart->add('B01');
		$cart->add('G01');

		$this->assertSame('4.95', $cart->getShippingTotal());
		$this->assertSame(Cart::ZERO, $cart->getDiscountTotal());
		$this->assertSame('37.85', $cart->getTotal());
	}

	public function testRedAndRedWidget()
	{
		$cart = new Cart($this->storage);
		$cart->add('R01');
		$cart->add('R01');

		$this->assertSame('4.95', $cart->getShippingTotal());
		$this->assertSame('16.48', $cart->getDiscountTotal());
		$this->assertSame('54.37', $cart->getTotal());
	}

	public function testRedAndGreenWidget()
	{
		$cart = new Cart($this->storage);
		$cart->add('R01');
		$cart->add('G01');

		$this->assertSame('2.95', $cart->getShippingTotal());
		$this->assertSame(Cart::ZERO, $cart->getDiscountTotal());
		$this->assertSame('60.85', $cart->getTotal());
	}

	public function test2BlueAnd3RedWidgets()
	{
		$cart = new Cart($this->storage);
		$cart->add('B01');
		$cart->add('B01');
		$cart->add('R01');
		$cart->add('R01');
		$cart->add('R01');

		$this->assertSame(Cart::ZERO, $cart->getShippingTotal());
		$this->assertSame('16.48', $cart->getDiscountTotal());
		$this->assertSame('98.27', $cart->getTotal());
	}

	public function testBuyTwoYellowGetThirdFree()
	{
		$cart = new Cart($this->storage);
		$cart->add('Y01');
		$cart->add('Y01');
		$cart->add('Y01');

		$this->assertSame(Cart::ZERO, $cart->getShippingTotal());
		$this->assertSame('99.99', $cart->getDiscountTotal());
		$this->assertSame('199.98', $cart->getTotal());
	}

	public function testFreeShipping()
	{
		$cart = new Cart($this->storage);
		$cart->add('R01');
		$cart->add('R01');
		$cart->add('R01');
		$cart->add('Y01');
		$cart->add('Y01');
		$cart->add('Y01');

		$this->assertSame(Cart::ZERO, $cart->getShippingTotal());
		$this->assertSame('99.99', $cart->getDiscountTotal());
		$this->assertSame('298.83', $cart->getTotal());
	}
}
