<?php

namespace ModularityGuides\Helper;

class Lang
{
    public static function getLang(): array
    {
        return [
            // partials/tot.blade.php
            'notice' => __('Notice', 'modularity-guides'),
            'send' => __('Send', 'modularity-guides'),
            'email' => __('Email', 'modularity-guides'),
            'send_todo_list' => __('Send todo-list as email', 'modularity-guides'),
            'send_as_email' => __('Send as email', 'modularity-guides'),
            'title' => __('Title', 'modularity-guides'),
            'link' => __('Link', 'modularity-guides'),
            'your_checklist' => __('Your checklist', 'modularity-guides'),
            // Module.php
            'email_sent'    => __("Email was sent", 'modularity-guides'),
            'email_failed'  => __("The message can't be sent right now. Please try again later.", 'modularity-guides'),
            'lockMessage'   => __("You need to check the required fields", 'modularity-guides'),
            'mailIntro'    => __('Hi, here\'s your requested checlist, enjoy!', 'modularity-guides'),

        ];
    }
}
