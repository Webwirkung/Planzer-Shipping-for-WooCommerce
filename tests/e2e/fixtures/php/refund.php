<?php
// wp eval-file refund.php <order id> '{"E2E-BEANIE":1}'  → refunds those quantities of the line items.
$order = wc_get_order((int) $args[0]);
$qty = json_decode($args[1], true);
$lines = [];
$total = 0;
foreach ($order->get_items() as $item_id => $item) {
    $sku = $item->get_product()->get_sku();
    if (empty($qty[$sku])) {
        continue;
    }
    $amount = $item->get_total() / $item->get_quantity() * $qty[$sku];
    $lines[$item_id] = ['qty' => $qty[$sku], 'refund_total' => $amount, 'refund_tax' => []];
    $total += $amount;
}
wc_create_refund(['order_id' => $order->get_id(), 'amount' => $total, 'line_items' => $lines, 'restock_items' => false]);
echo 'ok';
