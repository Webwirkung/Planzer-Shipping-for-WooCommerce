<?php
// IDs of the seeded products and shipping classes.
$out = ['products' => [], 'classes' => []];
foreach (['E2E-BEANIE', 'E2E-HOODIE', 'E2E-MUG'] as $sku) {
    $out['products'][$sku] = wc_get_product_id_by_sku($sku);
}
foreach (['small', 'bulky'] as $slug) {
    $out['classes'][$slug] = get_term_by('slug', $slug, 'product_shipping_class')->term_id;
}
echo wp_json_encode($out);
