<?php
/**
 * Plugin Name: Planzer e2e test hooks
 * Description: Loaded only in the e2e test environment (tests/e2e/docker-compose.yml). Never shipped.
 */

// All mail goes to Mailpit.
add_action('phpmailer_init', function ($mailer) {
    $mailer->isSMTP();
    $mailer->Host = 'mailpit';
    $mailer->Port = 1025;
    $mailer->SMTPAuth = false;
    $mailer->SMTPAutoTLS = false;
});
// "wordpress@localhost" is rejected by PHPMailer; real sites have a domain.
add_filter('wp_mail_from', fn () => 'shop@example.test');

// No calls to production services from tests.
add_filter('pre_http_request', function ($pre, $args, $url) {
    $host = (string) wp_parse_url($url, PHP_URL_HOST);

    if ('plugin.webwirkung.dev' === $host) {
        return ['headers' => [], 'body' => '{}', 'response' => ['code' => 200, 'message' => 'OK'], 'cookies' => [], 'filename' => null];
    }

    if ('api.planzergroup.com' === $host) {
        // Address validation: valid unless the street contains "Ungueltig".
        $body = json_decode((string) ($args['body'] ?? ''), true) ?: [];
        $valid = false === stripos((string) ($body['street'] ?? ''), 'Ungueltig');
        return [
            'headers' => [],
            'body' => wp_json_encode(['adressen' => $valid ? [$body] : []]),
            'response' => ['code' => 200, 'message' => 'OK'],
            'cookies' => [],
            'filename' => null,
        ];
    }

    return $pre;
}, 10, 3);

// S87: simulate api.qrserver.com being down. mPDF fetches the QR image with cURL, not wp_remote_*,
// so the URL is rewritten to an address that refuses connections.
add_filter('planzer/qr/generator/print_options', function ($options) {
    return $options;
});
add_filter('clean_url', function ($url) {
    if ('yes' === get_option('planzer_e2e_block_qrserver') && str_contains((string) $url, 'api.qrserver.com')) {
        return str_replace('https://api.qrserver.com', 'http://127.0.0.1:9', $url);
    }
    return $url;
}, 99);

// WooCommerce turns HPOS on for new shops on the first admin request (woocommerce_newly_installed).
// Keep the storage bin/setup.sh chose for this run.
add_filter('woocommerce_enable_hpos_by_default_for_new_shops', fn () => '0' !== get_option('planzer_e2e_hpos', '1'));

// Test clock for pickup/delivery dates (S50–S56). The option holds e.g. "2026-11-11 14:59".
add_filter('planzer/csv/now', function ($now) {
    $fixed = get_option('planzer_e2e_now');
    return $fixed ? new DateTimeImmutable($fixed, $now->getTimezone()) : $now;
});
