<?php

declare(strict_types=1);

namespace ModularityGuides\Helper\Wizard;

use WpService\Contracts\EscHtml;

/**
 * The three possible results (pass, stop, review) shown on the summary page.
 * Texts from the "Summary page" fields win over the default texts.
 */
class ResultFactory
{
    private const NOTICE_TYPES = ['pass' => 'success', 'stop' => 'danger', 'review' => 'warning'];
    private const NOTICE_ICONS = ['pass' => 'check_circle', 'stop' => 'cancel', 'review' => 'help'];

    public function __construct(
        private EscHtml $wpService,
    ) {}

    public function create(array $summaryPage, array $lang): array
    {
        return array_map(
            fn(string $type): array => $this->result($type, $summaryPage, $lang),
            OptionFactory::OUTCOMES,
        );
    }

    private function result(string $type, array $summaryPage, array $lang): array
    {
        $title = trim((string) ($summaryPage[$type . '_title'] ?? ''));
        $text = trim((string) ($summaryPage[$type . '_text'] ?? ''));

        return [
            'type' => $type,
            'noticeType' => self::NOTICE_TYPES[$type],
            'icon' => self::NOTICE_ICONS[$type],
            'title' => $title !== '' ? $title : $lang['wizard_' . $type . '_title'],
            // The reason is filled in by the script, so it needs its own hooks.
            'messageHtml' => '<strong class="mod-guide-wizard__hero-reason" ' . ViewModel::ATTRIBUTE_PREFIX . 'reason hidden></strong>'
                . '<span ' . ViewModel::ATTRIBUTE_PREFIX . 'hero-text>'
                . $this->wpService->escHtml($text !== '' ? $text : $lang['wizard_' . $type . '_text'])
                . '</span>',
        ];
    }
}
