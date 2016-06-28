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

        //add_action('wp_enqueue_scripts', array($this, 'style'));
        //add_action('wp_enqueue_scripts', array($this, 'script'));
    }

    public function style()
    {
        wp_register_style('modularity-guides', MODULARITYGUIDES_URL . '/dist/css/modularity-guides.min.css', null, '1.0.0');
        wp_enqueue_style('modularity-guides');
    }

    public function script()
    {
        wp_register_script('modularity-guides', MODULARITYGUIDES_URL . '/dist/js/modularity-guides.min.js', null, '1.0.0', true);
        wp_enqueue_script('modularity-guides');
    }

    public function jsonLoadPath($paths)
    {
        $paths[] = MODULARITYGUIDES_PATH . 'source/acf-json';
        return $paths;
    }
}
