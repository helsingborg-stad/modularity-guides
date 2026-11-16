<?php

/**
 * Plugin Name:       Modularity Guides
 * Plugin URI:        (#plugin_url#)
 * Description:       A addon for Modularity to create guides
 * Version: 5.0.16
 * Author:            Kristoffer Svanmark
 * Author URI:        (#plugin_author_url#)
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-guides
 * Domain Path:       /languages
 */

use AcfService\Implementations\NativeAcfService;
use WpService\Implementations\NativeWpService;

// Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYGUIDES_PATH', plugin_dir_path(__FILE__));
define('MODULARITYGUIDES_URL', plugins_url('', __FILE__));
define('MODULARITYGUIDES_TEMPLATE_PATH', MODULARITYGUIDES_PATH . 'templates/');
define('MODULARITYGUIDES_MODULE_VIEW_PATH', plugin_dir_path(__FILE__) . '/templates/');
define('MODULARITYGUIDES_API_NAMESPACE', 'wp/v2');

load_plugin_textdomain('modularity-guides', false, plugin_basename(dirname(__FILE__)) . '/languages');

// Autoload from plugin
if (file_exists(MODULARITYGUIDES_PATH . 'vendor/autoload.php')) {
    require_once MODULARITYGUIDES_PATH . 'vendor/autoload.php';
}
require_once MODULARITYGUIDES_PATH . 'Public.php';

add_filter(
    '/Modularity/externalViewPath',
    function ($arr) {
        $arr['mod-guide'] = MODULARITYGUIDES_MODULE_VIEW_PATH;
        return $arr;
    },
    10,
    3
);

// Acf auto import and export. Edit the field group in ACF locally; saving it re-exports
// source/php/AcfFields/php (translatable labels) and source/php/AcfFields/json.
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-guides');
    $acfExportManager->setExportFolder(MODULARITYGUIDES_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport([
        'modularity-guides-fields' => 'group_577279265d68a',
    ]);
    $acfExportManager->import();
});

// AcfExportManager only translates sub fields of repeaters and groups. Also translate the sub fields
// of flexible content layouts, so every label in the export gets __(). Only runs while exporting
// (acf_translate_field is a no-op unless ACF's l10n settings are enabled).
add_filter('acf/translate_field/type=flexible_content', static function (array $field): array {
    foreach ($field['layouts'] ?? [] as $layoutKey => $layout) {
        foreach ($layout['sub_fields'] ?? [] as $subFieldKey => $subField) {
            $field['layouts'][$layoutKey]['sub_fields'][$subFieldKey] = acf_translate_field($subField);
        }
    }

    return $field;
});

// AcfExportManager also wraps default values in __(). For choice fields the default value is a
// choice key (e.g. "accordion"), which must never be translated, so unwrap it again.
add_filter('acf/translate_field', static function (array $field): array {
    $isChoiceField = in_array($field['type'] ?? '', ['select', 'button_group', 'radio', 'checkbox'], true);

    if ($isChoiceField && is_string($field['default_value'] ?? null)) {
        $field['default_value'] = preg_replace("/^!!__\\(!!'(.*)!!', !!'[^!]*!!'\\)!!$/", '$1', $field['default_value']);
    }

    return $field;
}, 20);

// Migration from the accordion mode: wp modularity-guides migrate-to-wizard --dry-run
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command(
        'modularity-guides',
        new ModularityGuides\Migration\MigrateCommand(
            new ModularityGuides\Migration\WizardMigration(new NativeWpService()),
        ),
    );
}

// Start application
new ModularityGuides\App();
new ModularityGuides\Api(new NativeWpService(), new NativeAcfService());
