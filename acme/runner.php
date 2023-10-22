<?php

require __DIR__ . '/vendor/autoload.php';

use App\Cart\Cart;
use App\Cart\Product;
use App\Cart\DiscountRule;
use App\Cart\ShippingRule;
use App\Storage\Storage;

$json = file_get_contents('php://stdin');
$data = json_decode($json, true);

if (empty($data['Products']) || empty($data['Shipping']) || empty($data['Discounts']) || empty($data['Carts'])) {
    throw new \InvalidArgumentException('Invalid input');
}

$storage = new Storage();

foreach ($data['Products'] as $product) {
    $storage->addProduct(new Product($product['code'], $product['description'], $product['price']));
}

foreach ($data['Shipping'] as $shippingRule) {
    $storage->addShippingRule(new ShippingRule($shippingRule));
}

foreach ($data['Discounts'] as $discountRule) {
    $storage->addDiscountRule(new DiscountRule($discountRule, $storage));
}

foreach ($data['Carts'] as $cartItems) {
    $cart = new Cart($storage);
    $items = "";

    foreach ($cartItems as $item) {
        $code = $item['code'];
        $cart->add($code);
        $items .= "{$code}, ";
    }

    $items = rtrim($items, ', ');
    echo "--------" . PHP_EOL;
    echo "Items: {$items}" . PHP_EOL;
    echo "Subtotal: {$cart->getSubtotal()}" . PHP_EOL;
    echo "Shipping: {$cart->getShippingTotal()}" . PHP_EOL;
    echo "Discount: {$cart->getDiscountTotal()}" . PHP_EOL;
    echo "Total: {$cart->getTotal()}" . PHP_EOL;
}
