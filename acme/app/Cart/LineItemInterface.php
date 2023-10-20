<?php

namespace App\Cart;

interface LineItemInterface
{
    public function getCode() : string;
    public function getPrice() : string;
    public function getDescription() : string;
}
