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
        add_action('admin_notices', array($this, 'recaptchaConstant'));
    }

    public function recaptchaConstant()
    {
        if (defined('G_RECAPTCHA_KEY') && defined('G_RECAPTCHA_SECRET')) {
            return;
        }
        $class = 'notice notice-warning';
        $message = __('Modularity guides: constant \'g_recaptcha_key\' or \'g_recaptcha_secret\' is not defined.', 'modularity-guides' );
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }

    public function jsonLoadPath($paths)
    {
        $paths[] = MODULARITYGUIDES_PATH . 'source/acf-json';
        return $paths;
    }

    public function emailTodo()
    {
        if (!is_user_logged_in()) {
            $g_recaptcha_secret = defined('G_RECAPTCHA_SECRET') ? G_RECAPTCHA_SECRET : '';
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'secret=' . $g_recaptcha_secret . '&response=' . $_POST['captcha'] . '&remoteip=' . $_SERVER['REMOTE_ADDR']);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            $response = json_decode($response);

            curl_close($ch);

            if (!isset($response->success) || $response->success !== true) {
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
