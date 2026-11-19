<?php
// Reference data (docs/testing-concept.md §6). Run once after install: wp eval-file seed.php
foreach (require __DIR__ . '/settings-a.php' as $key => $value) {
    update_option($key, $value);
}
update_option('planzer_qr_sequence_number', 0);

// Store
update_option('woocommerce_default_country', 'CH:LU');
update_option('woocommerce_currency', 'CHF');
update_option('woocommerce_store_address', 'Musterstrasse 12');
update_option('woocommerce_store_city', 'Luzern');
update_option('woocommerce_store_postcode', '6003');
update_option('woocommerce_coming_soon', 'no');
update_option('woocommerce_onboarding_profile', ['skipped' => true]);
update_option('woocommerce_enable_guest_checkout', 'yes');
update_option('woocommerce_ship_to_destination', 'billing');

// Classic (shortcode) checkout and cart, so woocommerce_after_checkout_validation runs.
foreach (['cart' => '[woocommerce_cart]', 'checkout' => '[woocommerce_checkout]'] as $page => $shortcode) {
    $id = wp_insert_post(['post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Classic ' . $page, 'post_name' => 'classic-' . $page, 'post_content' => $shortcode]);
    update_option("woocommerce_{$page}_page_id", $id);
}

// Cash on delivery: orders go straight to "processing".
$cod = get_option('woocommerce_cod_settings', []);
update_option('woocommerce_cod_settings', array_merge($cod, ['enabled' => 'yes', 'enable_for_methods' => [], 'enable_for_virtual' => 'yes']));

// Shipping classes
$classes = [];
foreach (['small' => 'Small', 'bulky' => 'Bulky'] as $slug => $name) {
    $term = term_exists($slug, 'product_shipping_class') ?: wp_insert_term($name, 'product_shipping_class', ['slug' => $slug]);
    $classes[$slug] = (int) $term['term_id'];
}

// Products
$products = [
    ['E2E-BEANIE', 'Beanie', '20', 'small'],
    ['E2E-HOODIE', 'Hoodie', '45', 'bulky'],
    ['E2E-MUG', 'Mug', '10', 'small'],
];
foreach ($products as [$sku, $name, $price, $class]) {
    if (wc_get_product_id_by_sku($sku)) {
        continue;
    }
    $p = new WC_Product_Simple();
    $p->set_name($name);
    $p->set_sku($sku);
    $p->set_regular_price($price);
    $p->set_shipping_class_id($classes[$class]);
    $p->set_status('publish');
    $p->save();
}

// Shipping zone Switzerland: flat rate (per class) + free shipping.
$zone = new WC_Shipping_Zone();
$zone->set_zone_name('Switzerland');
$zone->add_location('CH', 'country');
$zone->save();
$flat_id = $zone->add_shipping_method('flat_rate');
update_option("woocommerce_flat_rate_{$flat_id}_settings", [
    'title' => 'Flat rate',
    'tax_status' => 'none',
    'cost' => '0',
    "class_cost_{$classes['small']}" => '5',
    "class_cost_{$classes['bulky']}" => '15',
    'no_class_cost' => '0',
    'type' => 'class',
]);
$zone->add_shipping_method('free_shipping');
update_option('planzer_e2e_flat_rate_instance', $flat_id);

echo "seeded\n";
