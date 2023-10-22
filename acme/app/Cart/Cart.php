<?php

namespace App\Cart;

use App\Storage\Storage;
use App\Storage\StorageInterface;
use Brick\Money\Money;

class Cart
{
	public const ZERO = '0.00';

	public function __construct(
		private StorageInterface $storage = new Storage(),
		private array $lineItems = [],
		private string $subTotal = '',
	) {
	}

	public function add(string $code) : void
	{
		$lineItem = $this->storage->getProduct($code);

		if (!$lineItem) {
			return;
		}

		$this->lineItems[] = $lineItem;
		$this->subTotal = '';
	}

	/**
	 * Get the total price of all line items in the cart.
	 */
	public function getSubtotal() : string
	{
		if ($this->subTotal !== '') {
			return $this->subTotal;
		}

		$total = array_reduce($this->lineItems, function ($subtotal, $lineItem) {
			return $subtotal->plus($lineItem->getPrice());
		}, Money::of(0, $this->storage->getCurrencyCode()));

		$this->subTotal = (string) $total->getAmount();

		return $this->subTotal;
	}

	public function getShippingTotal() : string
	{
		$validShippingRules = array_reduce($this->storage->getShippingRules(), function ($validShippingRules, $shippingRule) {
			if ($shippingRule->isSatisfiedBy($this)) {
				$validShippingRules[] = $shippingRule;
			}

			return $validShippingRules;
		}, []);

		if (empty($validShippingRules)) {
			return self::ZERO;
		}

		return array_reduce($validShippingRules, function ($cheapestShipping, $shippingRule) {
			if ($cheapestShipping === NULL
					|| $shippingRule->getPrice() < $cheapestShipping) {
				return $shippingRule->getPrice();
			}

			return $cheapestShipping;
		}, NULL);
	}

	public function getDiscountTotal() : string
	{
		$validDiscountRules = array_reduce($this->storage->getDiscountRules(), function ($validDiscountRules, $discountRule) {
			if ($discountRule->isSatisfiedBy($this)) {
				$validDiscountRules[] = $discountRule;
			}

			return $validDiscountRules;
		}, []);

		if (empty($validDiscountRules)) {
			return self::ZERO;
		}

		return array_reduce($validDiscountRules, function ($cheapestDiscount, $discountRule) {
			if ($cheapestDiscount === NULL
					|| $discountRule->getPrice() > $cheapestDiscount) {
				return $discountRule->getPrice();
			}

			return $cheapestDiscount;
		}, NULL);
	}

	public function getTotal() : string
	{
		$currencyCode = $this->storage->getCurrencyCode();
		$subTotal = Money::of($this->getSubtotal(), $currencyCode);
		$shippingTotal = Money::of($this->getShippingTotal(), $currencyCode);
		$discountTotal = Money::of($this->getDiscountTotal(), $currencyCode);

		return (string) $subTotal
				->plus($shippingTotal)
				->minus($discountTotal)
				->getAmount();
	}

	public function getCount() : int
	{
		return count($this->lineItems);
	}

	public function getLineItemCounts() : array
	{
		return array_reduce($this->lineItems, function ($lineItemCounts, $lineItem) {
			$code = $lineItem->getCode();
			$lineItemCounts[$code] = $lineItemCounts[$code] ?? 0;
			$lineItemCounts[$code]++;

			return $lineItemCounts;
		}, []);
	}
}
