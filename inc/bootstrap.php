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
;        return Planzer\planzer($moduleName);
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

if (! function_exists('isPlanzerWcPage')) {
    function isPlanzerWcPage(array $pages): bool
    {
        if (! is_admin()) {
            return false;
        }

        $page = isset($_GET['page']) ? sanitize_key((string) $_GET['page']) : '';

        return $page !== '' && in_array($page, $pages, true);
    }
}

add_action('init', function () {
    if (isPlanzerWcPage(['wc-orders', 'wc-settings'])) {
        planzerBoot();
    }
});
