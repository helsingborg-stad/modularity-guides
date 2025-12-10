<?php

declare(strict_types=1);

namespace ModularityGuides;

class App
{
    public function __construct()
    {
        add_action('init', static function () {
            if (function_exists('modularity_register_module')) {
                modularity_register_module(MODULARITYGUIDES_PATH . 'source/php/', 'Module');
            }
        });
        add_filter('acf/settings/load_json', [$this, 'jsonLoadPath']);
    }

    public function jsonLoadPath($paths)
    {
        $paths[] = MODULARITYGUIDES_PATH . 'source/acf-json';
        return $paths;
    }
}
