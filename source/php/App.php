<?php

namespace ModularityGuides;

class App
{
    public function __construct()
    {
        add_action(
            'plugins_loaded',
            function () {
                if (function_exists('modularity_register_module')) {
                    modularity_register_module(
                        MODULARITYGUIDES_PATH . 'source/php/',
                        'Module'
                    );
                }
            }
        );
        add_filter('acf/settings/load_json', array($this, 'jsonLoadPath'));
    }
    public function jsonLoadPath($paths)
    {
        $paths[] = MODULARITYGUIDES_PATH . 'source/acf-json';
        return $paths;
    }
}
