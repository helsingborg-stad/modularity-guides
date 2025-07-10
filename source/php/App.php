<?php

namespace ModularityGuides;

/**
 * Class App
 * @package ModularityGuides
 */
class App
{
    /**
     * App constructor.
     */
    public function __construct()
    {
        add_action('plugins_loaded', function () {
            if (function_exists('modularity_register_module')) {
                modularity_register_module(
                    MODULARITYGUIDES_PATH . 'source/php/', // The directory path of the module
                    'Module' // The class' file and class name (should be the same) withot .php extension
                );
            }
        });

        add_filter('acf/settings/load_json', array($this, 'jsonLoadPath'));
        add_action('wp_ajax_nopriv_email_todo', array($this, 'emailTodo'));
        add_action('wp_ajax_email_todo', array($this, 'emailTodo'));
    }

    /**
     * @param $paths
     * @return mixed
     */
    public function jsonLoadPath($paths)
    {
        $paths[] = MODULARITYGUIDES_PATH . 'source/acf-json';
        return $paths;
    }

    /**
     * Posting Email
     */
    public function emailTodo()
    {
        // Send the email
        $to = $_POST['email'];
        wp_mail(
            $to,
            __('Your checklist', 'modularity-guides'),
            urldecode($_POST['checklist']),
            array(
                'From: no-reply@helsingborg.se',
                'Content-Type: text/html; charset=UTF-8'
            )
        );

        wp_send_json(true);
        wp_die();
    }
}
