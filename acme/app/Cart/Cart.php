<?php

namespace App\Cart;

use App\Storage\Storage;
use App\Storage\StorageInterface;
use Brick\Money\Money;

class Cart
{
    public function __construct(
            private StorageInterface $storage = new Storage(),
            private array $lineItems = []
    ) {
    }

    public function add(string $code) : void
    {
        $lineItem = $this->storage->getProduct($code);

        if (!$lineItem) {
            return;
        }

        $this->lineItems[] = $lineItem;
    }

    /**
     * Get the total price of all line items in the cart.
     */
    public function getSubtotal() : string
    {
        $total = array_reduce($this->lineItems, function ($subtotal, $lineItem) {
            return $subtotal->plus($lineItem->getPrice());
        }, Money::of(0, $this->storage->getCurrencyCode()));

        return (string) $total->getAmount();
    }

    public function getCount() : int
    {
        return count($this->lineItems);
    }
}
