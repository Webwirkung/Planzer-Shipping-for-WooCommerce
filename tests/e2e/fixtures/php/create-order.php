<?php
// wp eval-file create-order.php '<json>'  → prints the new order ID.
// JSON: {"billing":{...},"shipping":{...}|null,"items":[["E2E-BEANIE",1]],"shipping_method":"flat_rate"|"free_shipping"|null,"status":"pending"}
$in = json_decode($args[0] ?? '{}', true) ?: [];

$order = wc_create_order(['status' => 'pending']);
foreach ($in['items'] ?? [['E2E-BEANIE', 1]] as [$sku, $qty]) {
    $order->add_product(wc_get_product(wc_get_product_id_by_sku($sku)), $qty);
}
$billing = $in['billing'] ?? [];
$order->set_address($billing, 'billing');
$shipping = array_key_exists('shipping', $in) ? $in['shipping'] : array_diff_key($billing, ['email' => 1, 'phone' => 1]);
if ($shipping) {
    $order->set_address($shipping, 'shipping');
}

$method = $in['shipping_method'] ?? 'flat_rate';
if ($method) {
    $zone = WC_Shipping_Zones::get_zone_matching_package(['destination' => ['country' => 'CH', 'state' => '', 'postcode' => '', 'city' => '']]);
    foreach ($zone->get_shipping_methods(true) as $instance) {
        if ($instance->id === $method) {
            $item = new WC_Order_Item_Shipping();
            $item->set_method_title($instance->get_title());
            $item->set_method_id($instance->id);
            $item->set_instance_id($instance->get_instance_id());
            $item->set_total($in['shipping_total'] ?? 0);
            $order->add_item($item);
        }
    }
}
$order->set_payment_method('cod');
$order->calculate_totals();
$order->save();

if (! empty($in['status']) && 'pending' !== $in['status']) {
    $order->update_status($in['status']);
}
echo $order->get_id();
