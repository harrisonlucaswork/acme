<?php

namespace App\Cart;

interface RuleInterface
{
	public function isSatisfiedBy(Cart $cart) : bool;
	public function getPrice() : string;
}
