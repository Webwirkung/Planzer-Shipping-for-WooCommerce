<?php

require PLANZER_ROOT_PATH . '/vendor/autoload.php';

if (! function_exists('planzerDoc') && function_exists('Planzer\\planzerDoc')) {
    function planzerDoc(): object
    {
        return Planzer\planzerDoc();
    }
}

if (! function_exists('planzer') && function_exists('Planzer\\planzer')) {
    function planzer(string $moduleName = ''): object
    {
        return Planzer\planzer($moduleName);
    }
}

if (! function_exists('createClass') && function_exists('Planzer\\createClass')) {
    function createClass(string $class, array $params = []): object
    {
        $object = new $class(...$params);
        planzerDoc()->addDocHooks($object);
        return $object;
    }
}

if (! function_exists('errorLog')) {
    function errorLog(\Throwable $error): void
    {
        error_log((string) $error);

        if (
            defined('WP_DEBUG') &&
            WP_DEBUG === true &&
            defined('WP_DEBUG_DISPLAY') &&
            WP_DEBUG_DISPLAY === true
        ) {
            dump($error);
        }
    }
}

if (! function_exists('planzerBoot')) {
    function planzerBoot(): void
    {
        static $booted = false;

        if ($booted) {
            return;
        }
        planzerDoc();
        planzer();

        $booted = true;
    }
}

add_action('admin_init', function () {
    $page      = isset($_GET['page'])       ? sanitize_key($_GET['page'])        : '';
    $postType  = isset($_GET['post_type'])  ? sanitize_key($_GET['post_type'])   : '';
    $postType2 = isset($_POST['post_type']) ? sanitize_key($_POST['post_type'])  : '';
    $post      = isset($_GET['post'])       ? (int) $_GET['post']                : 0;
    $postId    = isset($_POST['post_ID'])   ? (int) $_POST['post_ID']            : 0;

    $isWooPage     = strpos($page, 'wc-') === 0;
    $isLegacyOrder = $postType === 'shop_order' || $postType2 === 'shop_order'
        || ($post > 0 && get_post_type($post) === 'shop_order')
        || ($postId > 0 && get_post_type($postId) === 'shop_order');

    if ($isWooPage || $isLegacyOrder) {
        planzerBoot();
    }
});
