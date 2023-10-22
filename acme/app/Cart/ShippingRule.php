<?php

namespace App\Cart;

class ShippingRule implements RuleInterface
{
    public const SUBTOTAL_CONDITION = 'subtotal';
    public const ITEM_COUNT_CONDITION = 'item_count';
    public const GREATER_THAN = 'greater_than';
    public const LESS_THAN = 'less_than';

    private string $price;
    private array $conditions = [];

    public function __construct(array $rule)
    {
        if (empty($rule['price']) || empty($rule['conditions'])) {
            throw new \InvalidArgumentException('Invalid rule');
        }

        $this->price = $rule['price'];
        $this->conditions = $rule['conditions'];
    }

    public function isSatisfiedBy(Cart $cart) : bool
    {
        $satisfied = true;

        foreach ($this->conditions as $condition => $constraint) {
            $value = $condition === self::SUBTOTAL_CONDITION ? $cart->getSubtotal() : $cart->getCount();

            foreach ($constraint as $operator => $operand) {
                switch ($operator) {
                    case self::GREATER_THAN:
                        $satisfied &= $value > $operand;
                        break;
                    case self::LESS_THAN:
                        $satisfied &= $value < $operand;
                        break;
                    default:
                        $satisfied = false;
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
