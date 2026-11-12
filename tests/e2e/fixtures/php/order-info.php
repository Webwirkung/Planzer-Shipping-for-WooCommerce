<?php
// wp eval-file order-info.php <id> → JSON with what the tests assert on.
$order = wc_get_order((int) $args[0]);
$notes = array_map(fn ($n) => trim(wp_strip_all_tags(str_replace('<br>', "\n", $n->content))), wc_get_order_notes(['order_id' => $order->get_id(), 'order' => 'ASC']));
echo wp_json_encode([
    'id' => $order->get_id(),
    'status' => $order->get_status(),
    'tracking' => (string) $order->get_meta('planzer_tracking_code'),
    'notes' => array_values($notes),
    'sequence' => (int) get_option('planzer_qr_sequence_number'),
]);
