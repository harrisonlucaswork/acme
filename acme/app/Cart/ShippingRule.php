<?php

namespace App\Cart;

use App\Storage\Storage;
use App\Storage\StorageInterface;
use Brick\Money\Money;

class ShippingRule implements RuleInterface
{
	public const SUBTOTAL_CONDITION = 'subtotal';
	public const ITEM_COUNT_CONDITION = 'item_count';
	public const GREATER_THAN = 'greater_than';
	public const LESS_THAN = 'less_than';

	private string $price;
	private array $conditions = [];

	public function __construct(array $rule, private StorageInterface $storage = new Storage())
	{
		if (empty($rule['price']) || empty($rule['conditions'])) {
			throw new \InvalidArgumentException('Invalid rule');
		}

		$this->price = $rule['price'];
		$this->conditions = $rule['conditions'];
	}

	public function isSatisfiedBy(Cart $cart) : bool
	{
		$satisfied = TRUE;

		foreach ($this->conditions as $condition => $constraint) {
			if ($condition === self::SUBTOTAL_CONDITION) {
				$currencyCode = $this->storage->getCurrencyCode();
				$subtotal = Money::of($cart->getSubtotal(), $currencyCode);
				$discountTotal = Money::of($cart->getDiscountTotal(), $currencyCode);
				$value = floatval((string) $subtotal->minus($discountTotal)->getAmount());
			} else {
				$value = $cart->getCount();
			}

			foreach ($constraint as $operator => $operand) {
				switch ($operator) {
					case self::GREATER_THAN:
						$satisfied = $satisfied && ($value > $operand);
						break;
					case self::LESS_THAN:
						$satisfied = $satisfied && ($value < $operand);
						break;
					default:
						$satisfied = FALSE;
				}

				if (!$satisfied) {
					break;
				}
			}
		}

		return $satisfied;
	}

	public function getPrice() : string
	{
		return $this->price;
	}
}
