<?php

declare(strict_types=1);

namespace ModularityGuides\Admin;

use WpService\Contracts\AddAction;
use WpService\Contracts\AddFilter;
use WpService\Contracts\GetCurrentScreen;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\GetTheID;

/**
 * Editor helpers for answers:
 * - answers without a toggle key get one automatically when the guide is saved,
 * - "Show when" lists the guide's answers to choose from,
 * - toggle keys and relations of a question are hidden unless its "Show advanced fields" is on.
 *   They are only hidden visually (not with ACF conditional logic), so their values are always saved.
 */
class AnswerFields
{
    public const ANSWER_KEY_FIELD = 'field_57727e6d2981b';
    public const ADVANCED_FIELD = 'field_6912b1a0d0044';
    public const STEPS_FIELD = 'field_57727933e3560';
    public const POST_TYPE = 'mod-guide';

    public const SHOW_WHEN_FIELDS = [
        'field_6912b1a0d0060',
        'field_6912b1a0d0061',
        'field_6912b1a0d0062',
        'field_6912b1a0d0063',
    ];

    public function __construct(
        private AddAction&AddFilter&GetCurrentScreen&GetPostMeta&GetTheID $wpService,
    ) {}

    public function addHooks(): void
    {
        $this->wpService->addFilter('acf/update_value/key=' . self::ANSWER_KEY_FIELD, [$this, 'ensureAnswerKey']);

        foreach (self::SHOW_WHEN_FIELDS as $key) {
            $this->wpService->addFilter('acf/prepare_field/key=' . $key, [$this, 'addAnswerChoices']);
        }

        $this->wpService->addAction('acf/input/admin_footer', [$this, 'printAdminScript']);
    }

    /**
     * Guide edit screen only:
     * - questions start collapsed (showing their title), so editors open one at a time,
     * - every other row in the lists has a grey background, so new rows are easy to tell apart,
     * - fields with the wrapper class "mod-guide-advanced" are hidden in a question until its
     *   "Show advanced fields" switch is turned on.
     */
    public function printAdminScript(): void
    {
        $screen = $this->wpService->getCurrentScreen();
        if (($screen->post_type ?? '') !== self::POST_TYPE) {
            return;
        }

        printf(
            '<style>.acf-row.mod-guide-hide-advanced .acf-field.mod-guide-advanced{display:none}'
            // Every other row (questions, answers, todo items …) gets a light grey background.
            . '#acf-group_577279265d68a .acf-repeater>.acf-table>tbody>.acf-row:nth-child(even)>td{background:#f6f7f7}</style>'
            . '<script>(function($){if(!window.acf||!$){return;}var toggle=%1$s;'
            . 'function update(input){$(input).closest(".acf-row").toggleClass("mod-guide-hide-advanced",!input.checked);}'
            . 'function updateAll(){$(".acf-field[data-key="+toggle+"] input[type=checkbox]").each(function(){update(this);});}'
            . '$(document).on("change",".acf-field[data-key="+toggle+"] input[type=checkbox]",function(){update(this);});'
            . 'acf.addAction("ready",function(){updateAll();acf.getFields({key:%2$s}).forEach(function(steps){steps.$rows().addClass("-collapsed");});});'
            . 'acf.addAction("append",updateAll);})(window.jQuery);</script>',
            json_encode(self::ADVANCED_FIELD),
            json_encode(self::STEPS_FIELD),
        );
    }

    public function ensureAnswerKey(mixed $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : self::generateKey();
    }

    /**
     * @param array|false $field
     * @return array|false
     */
    public function addAnswerChoices(mixed $field): mixed
    {
        if (!is_array($field)) {
            return $field;
        }

        $postId = (int) $this->wpService->getTheID();
        $choices = $postId > 0 ? (new AnswerChoices($this->wpService))->forGuide($postId) : [];

        // Keep saved values that no longer match an answer, so nothing is lost silently.
        foreach ((array) ($field['value'] ?? []) as $saved) {
            $saved = (string) $saved;
            if ($saved !== '' && !isset($choices[$saved])) {
                $choices[$saved] = $saved;
            }
        }

        $field['choices'] = $choices;

        return $field;
    }

    public static function generateKey(): string
    {
        return 'answer-' . bin2hex(random_bytes(4));
    }
}
