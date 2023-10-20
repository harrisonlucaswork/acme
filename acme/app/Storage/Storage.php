<?php

namespace App\Storage;

use App\Cart\LineItemInterface;

class Storage implements StorageInterface
{
    public function __construct(private array $products = [])
    {
    }

    public function getProduct(string $code) : ?LineItemInterface
    {
        return $this->products[$code] ?? null;
    }

    public function getCurrencyCode() : string
    {
        return 'USD';
    }
}
