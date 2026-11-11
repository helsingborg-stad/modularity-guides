<?php

declare(strict_types=1);

namespace ModularityGuides\Helper;

class Lang
{
    public static function getLang(): array
    {
        return [
            ...self::getWizardLang(),
            ...self::getWizardResultLang(),
            'notice' => __('Notice', 'modularity-guides'),
            'send' => __('Send', 'modularity-guides'),
            'email' => __('Email', 'modularity-guides'),
            'send_todo_list' => __('Send todo-list as email', 'modularity-guides'),
            'send_as_email' => __('Send as email', 'modularity-guides'),
            'your_checklist' => __('Your checklist', 'modularity-guides'),
            'email_error' => __('You need to add a valid E-mail!', 'modularity-guides'),
            'email_sent' => __('Email was sent', 'modularity-guides'),
            'email_failed' => __("The message can't be sent right now. Please try again later.", 'modularity-guides'),
            'email_intro' => __('Hi, here\'s your requested checlist, enjoy!', 'modularity-guides'),
            'read_more' => __('Read more', 'modularity-guides'),
        ];
    }

    /**
     * Strings for the step by step (wizard) display mode.
     */
    private static function getWizardLang(): array
    {
        return [
            'wizard_progress_label' => __('Your progress through the guide', 'modularity-guides'),
            /* translators: 1: current question number, 2: number of questions */
            'wizard_question_x_of_y' => __('Question %1$s of %2$s', 'modularity-guides'),
            /* translators: %s: number of answered questions */
            'wizard_answered' => _n_noop('%s question answered', '%s questions answered', 'modularity-guides'),
            /* translators: %s: number of questions left */
            'wizard_remaining' => _n_noop('%s question remains.', '%s questions remain.', 'modularity-guides'),
            'wizard_continue' => __('Continue', 'modularity-guides'),
            'wizard_back' => __('Back', 'modularity-guides'),
            'wizard_show_summary' => __('Show summary', 'modularity-guides'),
            'wizard_choose_one' => __('Choose an answer to continue.', 'modularity-guides'),
            'wizard_choose_many' => __('Choose one or more answers to continue.', 'modularity-guides'),
            'wizard_saved_answer' => __('Your saved answer is selected. You can choose another.', 'modularity-guides'),
            'wizard_note_pass' => __('Your answer takes you further in the guide.', 'modularity-guides'),
            'wizard_note_stop' => __('This answer stops the guide. Continue to see why and what to do next.', 'modularity-guides'),
            'wizard_note_review' => __('This answer needs to be checked. Continue to see what is missing.', 'modularity-guides'),
            'wizard_tag_pass' => _x('Continue', 'answer outcome', 'modularity-guides'),
            'wizard_tag_stop' => _x('Stop', 'answer outcome', 'modularity-guides'),
            'wizard_tag_review' => _x('Check', 'answer outcome', 'modularity-guides'),
            'wizard_help' => __('Help with the question', 'modularity-guides'),
            'wizard_summary' => __('Summary', 'modularity-guides'),
            'wizard_your_answers' => __('Your answers', 'modularity-guides'),
            'wizard_edit' => __('Edit', 'modularity-guides'),
            'wizard_not_answered' => __('Not answered', 'modularity-guides'),
            'wizard_outcome_stop' => __('Stops the guide', 'modularity-guides'),
            'wizard_outcome_review' => __('Needs to be checked', 'modularity-guides'),
            /* translators: %s: short title of the question where the guide stopped */
            'wizard_stopped_at' => __('The guide stopped at "%s". The other questions have not been assessed.', 'modularity-guides'),
            'wizard_print' => __('Print / save as PDF', 'modularity-guides'),
            'wizard_download' => __('Save answers as text', 'modularity-guides'),
            'wizard_download_done' => __('Your answers have been saved as a text file.', 'modularity-guides'),
            'wizard_restart' => __('Start over', 'modularity-guides'),
            'wizard_restart_title' => __('Do you want to start over?', 'modularity-guides'),
            'wizard_restart_text' => __('Your answers will be cleared. Save the summary first if you want to keep it.', 'modularity-guides'),
            'wizard_restart_confirm' => __('Yes, start over', 'modularity-guides'),
            'wizard_cancel' => __('Cancel', 'modularity-guides'),
            'wizard_created' => __('Created', 'modularity-guides'),
        ];
    }

    /**
     * Default result texts for the step by step mode, used when the summary page fields are empty.
     */
    private static function getWizardResultLang(): array
    {
        return [
            'wizard_pass_title' => __('You have answered all questions', 'modularity-guides'),
            'wizard_pass_text' => __('Here is a summary of your answers and what you can do next.', 'modularity-guides'),
            'wizard_stop_title' => __('The guide stops here', 'modularity-guides'),
            'wizard_stop_text' => __('One of your answers does not meet a condition in the guide. Contact us for advice on how to proceed.', 'modularity-guides'),
            'wizard_review_title' => __('This needs to be checked', 'modularity-guides'),
            'wizard_review_text' => __('The guide cannot give a preliminary answer when information is unknown. Check the information and update your answer.', 'modularity-guides'),
        ];
    }
}
