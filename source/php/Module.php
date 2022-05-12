<?php

namespace ModularityGuides;

use HelsingborgStad\RecaptchaIntegration as Captcha;

class Module extends \Modularity\Module
{
    public $slug = 'guide';
    public $icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1MTIgNTEyIj48cGF0aCBkPSJNMzMyLjEgMEg1NS41djUxMmg0MDEuMVYxMjQuNUwzMzIuMSAwek0yMDUuOSAzNTYuNWwtNDIuNyAzNC4xYy0zLjQgMi43LTguMyAyLjUtMTEuNC0uNmwtMTctMTdjLTMuMy0zLjQtMy4yLTguOC4yLTEyLjEgMy4zLTMuMiA4LjUtMy4yIDExLjkgMGwxMS43IDExLjcgMzYuNy0yOS40YzMuNy0zIDktMi40IDEyIDEuMyAyLjggMy43IDIuMiA5LjEtMS40IDEyem0wLTkzLjhsLTQyLjcgMzQuMWMtMy40IDIuNy04LjMgMi41LTExLjQtLjZsLTE3LjEtMTcuMWMtMy4zLTMuNC0zLjItOC44LjItMTIuMSAzLjMtMy4yIDguNS0zLjIgMTEuOSAwbDExLjcgMTEuNyAzNi43LTI5LjRjMy43LTMgOS0yLjQgMTIgMS4zczIuMyA5LjEtMS4zIDEyLjF6bTAtOTMuOWwtNDIuNyAzNC4xYy0zLjQgMi43LTguMyAyLjUtMTEuNC0uNmwtMTcuMS0xNy4xYy0zLjMtMy40LTMuMi04LjguMi0xMi4xIDMuMy0zLjIgOC41LTMuMiAxMS45IDBsMTEuNyAxMS43IDM2LjctMjkuNGMzLjctMyA5LTIuNCAxMiAxLjNzMi4zIDkuMS0xLjMgMTIuMXpNMzYyLjcgMzg0SDIyNi4xYy00LjcgMC04LjUtMy44LTguNS04LjVzMy44LTguNSA4LjUtOC41aDEzNi41YzQuNyAwIDguNSAzLjggOC41IDguNS4xIDQuNy0zLjcgOC41LTguNCA4LjV6bTAtOTMuOUgyMjYuMWMtNC43IDAtOC41LTMuOC04LjUtOC41czMuOC04LjUgOC41LTguNWgxMzYuNWM0LjcgMCA4LjUgMy44IDguNSA4LjVzLTMuNyA4LjUtOC40IDguNXptMC05My44SDIyNi4xYy00LjcgMC04LjUtMy44LTguNS04LjVzMy44LTguNSA4LjUtOC41aDEzNi41YzQuNyAwIDguNSAzLjggOC41IDguNS4xIDQuNi0zLjcgOC41LTguNCA4LjV6TTMyMCAxMzYuNVYxNy4xbDExOS41IDExOS41SDMyMHoiLz48L3N2Zz4=';
    public $supports = array();
    public $isBlockCompatible = false;

    public $templateDir = MODULARITYGUIDES_TEMPLATE_PATH;

    public function init()
    {
        $this->nameSingular = __('Guide', 'modularity');
        $this->namePlural = __('Guides', 'modularity');
        $this->description = __('A step by step guide', 'modularity');
    }

    public function data() : array
    {
        $data = array();
        $data['steps'] = get_field('steps', $this->ID);
        $theme = wp_get_theme();
        $data['municipio'] = ($theme->name == 'Municipio' || $theme->parent_theme == 'Municipio') ? true : false;

        $data['googleCaptchaTerms'] = __('This site is protected by reCAPTCHA and the Google <a href="https://policies.google.com/privacy">Privacy Policy</a> and <a href="https://policies.google.com/terms">Terms of Service</a> apply.', 'modularity-guides');

        return $data;
    }

    public function script()
    {

        wp_register_script('modularity-guides', MODULARITYGUIDES_URL . '/dist/'. Helper\CacheBust::name('js/modularity-guides.js'), null, '1.0.0', true);
        wp_localize_script('modularity-guides', 'guides', array(
            'email_sent'    => __("Email was sent", 'modularity-guides'),
            'email_failed'  => __("The message can't be sent right now. Please try again later.", 'modularity-guides'),
            'lockMessage'   => __("You need to check the required fields", 'modularity-guides'),
        ));
        wp_enqueue_script('modularity-guides');

        wp_register_style('modularity-guides', MODULARITYGUIDES_URL . '/dist/'. Helper\CacheBust::name('css/modularity-guides.css'), null, '1.0.0');
        wp_enqueue_style('modularity-guides');

        if (wp_script_is('jquery', 'registered') && !wp_script_is('jquery', 'enqueued')) {
            wp_enqueue_script('jquery');
        }
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization (if you must, use __construct with care, this will probably break stuff!!)
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */
}
