<?php
// Resets the plugin to settings set A before each test.
// JSON: {"options":{"planzer_…":"…"},"now":"2026-11-11 10:00"|null}
$in = json_decode($args[0] ?? '{}', true) ?: [];
foreach (array_merge(require __DIR__ . '/settings-a.php', $in['options'] ?? []) as $key => $value) {
    update_option($key, $value);
}
if (! empty($in['now'])) {
    update_option('planzer_e2e_now', $in['now']);
} else {
    delete_option('planzer_e2e_now');
}
$flat = get_option('planzer_e2e_flat_rate_instance');
$settings = get_option("woocommerce_flat_rate_{$flat}_settings", []);
$settings['type'] = $in['flat_rate_type'] ?? 'class';
update_option("woocommerce_flat_rate_{$flat}_settings", $settings);
echo wp_json_encode(['sequence' => (int) get_option('planzer_qr_sequence_number')]);
