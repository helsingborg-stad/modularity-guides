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

    public function emailTodo()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'secret=6Lc7xSkTAAAAAIhub2lk0HY9EvxlP81vpen2AfDG&response=' . $_POST['captcha'] . '&remoteip=' . $_SERVER['REMOTE_ADDR']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $response = json_decode($response);

        curl_close($ch);

        if (!isset($response->success) || $response->success !== true) {
            echo "false";
            wp_die();
        }

        // Send the email
        $to = $_POST['email'];
        $mail = wp_mail(
            $to,
            __('Your checklist', 'modularity-guides'),
            __('Hi, here\'s your requested checlist, enjoy!', 'modularity-guides') . '<br><br>' . $_POST['checklist'],
            array(
                'From: no-reply@helsingborg.se',
                'Content-Type: text/html; charset=UTF-8'
            )
        );

        echo "success";
        wp_die();
    }
}
