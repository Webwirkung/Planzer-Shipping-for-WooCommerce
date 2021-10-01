<?php
require PLANZER_ROOT_PATH . '/vendor/autoload.php';

if (!function_exists('planzerDoc') && function_exists('Planzer\\planzerDoc')) {
  function planzerDoc(): object
  {
    return Planzer\planzerDoc();
  }
}

if (!function_exists('planzer') && function_exists('Planzer\\planzer')) {
  function planzer(string $moduleName = ''): object
  {
    return Planzer\planzer($moduleName);
  }
}

if (!function_exists('createClass') && function_exists('Planzer\\createClass')) {
  function createClass(string $class, array $params = array()): object
  {
    $object = new $class(...$params);
    planzerDoc()->addDocHooks($object);
    return $object;
  }
}

if (!function_exists('errorLog')) {
  function errorLog(\Throwable $error): void
  {
    error_log($error);
    if (defined('WP_DEBUG') && true === WP_DEBUG && defined('WP_DEBUG_DISPLAY') && true === WP_DEBUG_DISPLAY) {
      dump($error);
    }
  }
}

planzerDoc();
planzer();
