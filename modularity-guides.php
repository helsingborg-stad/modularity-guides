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

load_plugin_textdomain('modularity-guides', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once MODULARITYGUIDES_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once MODULARITYGUIDES_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new ModularityGuides\Vendor\Psr4ClassLoader();
$loader->addPrefix('ModularityGuides', MODULARITYGUIDES_PATH);
$loader->addPrefix('ModularityGuides', MODULARITYGUIDES_PATH . 'source/php/');
$loader->register();

// Start application
new ModularityGuides\App();
