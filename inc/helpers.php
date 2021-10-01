<?php
namespace Planzer;

use Planzer\Init;
use Planzer\Core\DocHooks;

if (!function_exists('Planzer\\planzerDoc')) {
  function planzerDoc()
  {
    return DocHooks::get();
  }
}

if (!function_exists('Planzer\\planzer')) {
  function planzer(string $moduleName = '')
  {
    $modules = Init::get();
    if ('' === $moduleName) {
      return $modules;
    }
    if (!array_key_exists($moduleName, $modules->public)) {
      throw new \Exception(sprintf(__('Module %1$s doesn\'t exists!', 'planzer'), $moduleName));
    }
    return $modules->public[$moduleName];
  }
}

if (!function_exists('Planzer\\createClass')) {
  /**
   * Create instance of Class
   *
   * @see https://gist.github.com/nikic/6390366
   *
   * @param string $class
   * @param array $params
   * @return object
   */
  function createClass(string $class, array $params = array()): object
  {
    $object = new $class(...$params);
    planzerDoc()->addDocHooks($object);
    return $object;
  }
}
