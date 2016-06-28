<?php

namespace ModularityGuides;

class App
{
    public function __construct()
    {
        add_action('Modularity', function () {
            new \ModularityGuides\Module();
        });

        add_filter('acf/settings/load_json', array($this, 'jsonLoadPath'));
    }

    public function jsonLoadPath($paths)
    {
        $paths[] = MODULARITYGUIDES_PATH . 'source/acf-json';
        return $paths;
    }
}
