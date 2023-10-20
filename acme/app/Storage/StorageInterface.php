<?php

namespace App\Storage;

use App\Cart\LineItemInterface;

interface StorageInterface
{
    public function getProduct(string $code) : ?LineItemInterface;
    public function getCurrencyCode() : string;
}