<?php

namespace ModularityGuides;

use HelsingborgStad\RecaptchaIntegration as Captcha;

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
        add_action('wp_enqueue_scripts', array($this, 'addRecaptchaScript'), 40);

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
     * Check reCaptcha Keys and if poster is Human or bot.
     */
    public static function reCaptchaValidation()
    {
        if (is_user_logged_in()) {
            return;
        }

        Captcha::initCaptcha();
    }

    /**
     * Add Recaptcha Script
     */
    public static function addRecaptchaScript()
    {
        // If Captcha Script is not Enqueued
        if (!wp_script_is('municipio-google-recaptcha')) {
            Captcha::initScripts();
        }
    }


    /**
     * Posting Email
     */
    public function emailTodo()
    {

        if (!is_user_logged_in()) {
            $_POST['g-recaptcha-response'] = $_POST['captcha'];
            self::reCaptchaValidation();
        }

        $siteDomain = preg_replace( '/www\./i', '', parse_url( get_home_url() )['host'] );
        $siteMailFromDomain = defined( 'MOD_GUIDES_MAIL_FROM_DOMAIN' ) && !empty( MOD_GUIDES_MAIL_FROM_DOMAIN ) ? MOD_GUIDES_MAIL_FROM_DOMAIN : $siteDomain;
        $siteMailFromName = defined( 'MOD_GUIDES_MAIL_FROM_NAME' ) && !empty( MOD_GUIDES_MAIL_FROM_NAME ) ? MOD_GUIDES_MAIL_FROM_NAME : get_bloginfo( 'name' );

        // Send the email
        $to = $_POST['email'];
        wp_mail(
            $to,
            __('Your checklist', 'modularity-guides'),
            __('Hi, here\'s your requested checlist, enjoy!', 'modularity-guides') . '<br><br>' . urldecode($_POST['checklist']),
            array(
                'From: ' . $siteMailFromName . ' <no-reply@' . $siteMailFromDomain . '>',
                'Content-Type: text/html; charset=UTF-8'
            )
        );

        wp_send_json(true);
        wp_die();
    }
}
