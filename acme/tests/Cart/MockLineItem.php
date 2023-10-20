<?php

namespace Tests\Cart;

class MockLineItem implements \App\Cart\LineItemInterface
{
    public function __construct(
        private string $code,
        private string $price,
        private string $description = ''
    ) {
    }

    public function getCode() : string
    {
        return $this->code;
    }

    public function getPrice() : string
    {
        return $this->price;
    }

    public function getDescription() : string
    {
        return $this->description;
    }
}
