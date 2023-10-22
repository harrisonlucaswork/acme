<?php

namespace App\Cart;

class Product implements LineItemInterface
{
	public function __construct(
		private string $code,
		private string $description,
		private string $price,
	) {
	}

	public function getCode() : string
	{
		return $this->code;
	}

	public function getDescription() : string
	{
		return $this->description;
	}

	public function getPrice() : string
	{
		return $this->price;
	}
}
