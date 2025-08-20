<?php

namespace ModularityGuides\Helper;

class Lang
{
    public static function getLang(): array
    {
        return [
            'notice' => __('Notice', 'modularity-guides'),
            'send' => __('Send', 'modularity-guides'),
            'email' => __('Email', 'modularity-guides'),
            'send_todo_list' => __('Send todo-list as email', 'modularity-guides'),
            'send_as_email' => __('Send as email', 'modularity-guides'),
            'your_checklist' => __('Your checklist', 'modularity-guides'),
            'email_error'   => __('You need to add a valid E-mail!', 'modularity-guides'),
            'email_sent'    => __("Email was sent", 'modularity-guides'),
            'email_failed'  => __("The message can't be sent right now. Please try again later.", 'modularity-guides'),
            'email_intro'    => __('Hi, here\'s your requested checlist, enjoy!', 'modularity-guides'),
            'previous'      => __('Previous', 'modularity-guides'),
            'next'          => __('Next', 'modularity-guides'),
            'required'    => __('required', 'modularity-guides'),
            'read_more' => __('Read more', 'modularity-guides'),
        ];
    }
}
