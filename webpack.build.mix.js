/**
 * Build: Laravel Mix
 *
 * @see https://laravel.com/docs/5.8/mix
 * @author MC
 */

const mix = require('laravel-mix');
const path = require('path');

Mix.manifest.refresh = _ => void 0;
const rootPath = path.join(__dirname);
const pluginResources = path.join(__dirname, 'resources');

mix.copy(`${rootPath}/wc-planzer-shipping.php`, `build/wc-planzer-shipping/`);
mix.copy(`${rootPath}/readme*.txt`, `build/wc-planzer-shipping/`);
mix.copyDirectory(`${rootPath}/inc`, `build/wc-planzer-shipping/inc`);
mix.copyDirectory(`${rootPath}/src`, `build/wc-planzer-shipping/src`);
mix.copyDirectory(`${rootPath}/vendor`, `build/wc-planzer-shipping/vendor`);
mix.copyDirectory(`${rootPath}/dist`, `build/wc-planzer-shipping/dist`);
mix.copyDirectory(`${pluginResources}/views`, `build/wc-planzer-shipping/resources/views`);
mix.copyDirectory(`${pluginResources}/lang`, `build/wc-planzer-shipping/resources/lang`);
