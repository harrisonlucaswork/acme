<?php

namespace App\Storage;

use App\Cart\LineItemInterface;
use App\Cart\RuleInterface;

class Storage implements StorageInterface
{
    public function __construct(
            private array $products = [],
            private array $shippingRules = [],
            private array $discountRules = [],
    ) {}

    public function getProduct(string $code) : ?LineItemInterface
    {
        return $this->products[$code] ?? null;
    }

    public function getCurrencyCode() : string
    {
        return 'USD';
    }

    public function getShippingRules() : array
    {
        return $this->shippingRules;
    }

    public function getDiscountRules() : array
    {
        return $this->discountRules;
    }

    public function addProduct(LineItemInterface $product) : void
    {
        $this->products[$product->getCode()] = $product;
    }

    public function addShippingRule(RuleInterface $shippingRule) : void
    {
        $this->shippingRules[] = $shippingRule;
    }

    public function addDiscountRule(RuleInterface $discountRule) : void
    {
        $this->discountRules[] = $discountRule;
    }
}
