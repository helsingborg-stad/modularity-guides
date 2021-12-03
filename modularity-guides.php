<?php

/**
 * Plugin Name:       Modularity Guides
 * Plugin URI:        (#plugin_url#)
 * Description:       A addon for Modularity to create guides
 * Version:           1.0.0
 * Author:            Kristoffer Svanmark
 * Author URI:        (#plugin_author_url#)
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-guides
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYGUIDES_PATH', plugin_dir_path(__FILE__));
define('MODULARITYGUIDES_URL', plugins_url('', __FILE__));
define('MODULARITYGUIDES_TEMPLATE_PATH', MODULARITYGUIDES_PATH . 'templates/');
define('MODULARITYGUIDES_MODULE_VIEW_PATH', plugin_dir_path(__FILE__) . '/templates/');

load_plugin_textdomain('modularity-guides', false, plugin_basename(dirname(__FILE__)) . '/languages');

// Autoload from ABSPATH first and plugin path as alternative.
$rootAutoload = dirname(ABSPATH) . '/vendor/autoload.php';
$pluginAutoload = MODULARITYGUIDES_PATH . '/vendor/autoload.php';

if (file_exists($rootAutoload)) {
    require_once $rootAutoload;
} elseif (file_exists($pluginAutoload)) {
    require_once $pluginAutoload;
} else {
    error_log('Missing autoload.php, have you run composer install?');
}


require_once MODULARITYGUIDES_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once MODULARITYGUIDES_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new ModularityGuides\Vendor\Psr4ClassLoader();
$loader->addPrefix('ModularityGuides', MODULARITYGUIDES_PATH);
$loader->addPrefix('ModularityGuides', MODULARITYGUIDES_PATH . 'source/php/');
$loader->register();

add_filter( '/Modularity/externalViewPath', function($arr)
{
    $arr['mod-guide'] = MODULARITYGUIDES_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3
);

// Start application
new ModularityGuides\App();
