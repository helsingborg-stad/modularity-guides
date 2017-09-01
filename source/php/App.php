<?php

namespace ModularityGuides;

class App
{
    public function __construct()
    {
        add_action('plugins_loaded', function () {
            modularity_register_module(
                MODULARITYGUIDES_PATH . 'source/php/', // The directory path of the module
                'Module' // The class' file and class name (should be the same) withot .php extension
            );
        });

        add_filter('acf/settings/load_json', array($this, 'jsonLoadPath'));
        add_action('wp_ajax_nopriv_email_todo', array($this, 'emailTodo'));
        add_action('wp_ajax_email_todo', array($this, 'emailTodo'));
    }

    public function jsonLoadPath($paths)
    {
        $paths[] = MODULARITYGUIDES_PATH . 'source/acf-json';
        return $paths;
    }

    public function emailTodo()
    {
        if (!is_user_logged_in()) {
            $response = isset($_POST['captcha']) ? esc_attr($_POST['captcha']) : '';
            $reCaptcha = \Municipio\Helper\ReCaptcha::controlReCaptcha($response);

            if (!$reCaptcha) {
                echo "false";
                wp_die();
            }
        }

        // Send the email
        $to = $_POST['email'];
        $mail = wp_mail(
            $to,
            __('Your checklist', 'modularity-guides'),
            __('Hi, here\'s your requested checlist, enjoy!', 'modularity-guides') . '<br><br>' . urldecode($_POST['checklist']),
            array(
                'From: no-reply@helsingborg.se',
                'Content-Type: text/html; charset=UTF-8'
            )
        );

        echo "success";
        wp_die();
    }
}
