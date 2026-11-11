<?php

declare(strict_types=1);

namespace ModularityGuides;

class App
{
    public function __construct()
    {
        (new Admin\AnswerFields(new \WpService\Implementations\NativeWpService()))->addHooks();

        add_action('init', static function () {
            if (function_exists('modularity_register_module')) {
                modularity_register_module(MODULARITYGUIDES_PATH . 'source/php/', 'Module');
            }
        });
    }
}
