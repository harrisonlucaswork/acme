<?php

namespace App\Cart;

use App\Storage\StorageInterface;
use App\Storage\Storage;
use Brick\Money\Money;


class DiscountRule implements RuleInterface
{
    public const BUY_ONE_GET_SECOND_DISCOUNT = 'buy-one-get-second-discount';
    public const BUY_TWO_GET_THIRD_DISCOUNT = 'buy-two-get-third-discount';

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
        $lineItemCounts = $cart->getLineItemCounts();

        switch (array_key_first($this->conditions)) {
            case self::BUY_ONE_GET_SECOND_DISCOUNT:
                $satisfied = $lineItemCounts[$this->conditions[self::BUY_ONE_GET_SECOND_DISCOUNT]] > 1;
                break;
            case self::BUY_TWO_GET_THIRD_DISCOUNT:
                $satisfied = $lineItemCounts[$this->conditions[self::BUY_TWO_GET_THIRD_DISCOUNT]] > 2;
                break;
            default:
                $satisfied = false;
        }

        return $satisfied;
    }

    public function getPrice() : string
    {
        $lineItemCode = $this->conditions[array_key_first($this->conditions)];
        $lineItem = $this->storage->getProduct($lineItemCode);

        if (!$lineItem) {
            return '0.00';
        }

        $lineItemPrice = Money::of($lineItem->getPrice(), $this->storage->getCurrencyCode());
        $discount = $lineItemPrice->multipliedBy($this->price);

        return (string) $discount->getAmount();
    }
}
