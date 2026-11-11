<?php

declare(strict_types=1);

namespace ModularityGuides\Helper\Wizard;

use WpService\Contracts\EscHtml;

/**
 * Turns a "checkboxes" row into an answer option for the step by step mode.
 */
class OptionFactory
{
    public const OUTCOMES = ['pass', 'stop', 'review'];

    public function __construct(
        private EscHtml $wpService,
    ) {}

    /**
     * @param array  $checkbox ACF row: key, label, relate_to, description, outcome, outcome_reason
     * @param string $type     Input type, radio or checkbox
     * @param string $id       Unique element id
     * @param string $name     Input name shared by the question's options
     * @param string $fallback Value used when the row has no toggle key
     */
    public function create(array $checkbox, string $type, string $id, string $name, string $fallback): array
    {
        $outcome = self::normalizeOutcome($checkbox['outcome'] ?? null);
        $key = trim((string) ($checkbox['key'] ?? ''));

        return [
            'id' => $id,
            'name' => $name,
            'value' => $key !== '' ? $key : $fallback,
            'type' => $type,
            'labelHtml' => $this->labelHtml((string) ($checkbox['label'] ?? ''), (string) ($checkbox['description'] ?? '')),
            'outcome' => $outcome,
            'attributes' => $this->attributes($checkbox, $outcome, $key, $type),
        ];
    }

    public static function normalizeOutcome(mixed $outcome): string
    {
        return in_array($outcome, self::OUTCOMES, true) ? (string) $outcome : 'pass';
    }

    private function attributes(array $checkbox, string $outcome, string $key, string $type): array
    {
        $attributes = [
            ViewModel::ATTRIBUTE_PREFIX . 'outcome' => $outcome,
            ViewModel::ATTRIBUTE_PREFIX . 'reason' => (string) ($checkbox['outcome_reason'] ?? ''),
            ViewModel::ATTRIBUTE_PREFIX . 'label' => (string) ($checkbox['label'] ?? ''),
        ];

        if ($key !== '') {
            $attributes['data-mod-guide-toggle-key'] = $key;
        }

        // Relations toggle other checkboxes, which makes no sense for single choice.
        $relation = trim((string) ($checkbox['relate_to'] ?? ''));
        if ($relation !== '' && $type === 'checkbox') {
            $attributes['data-mod-guide-relation'] = $relation;
        }

        return $attributes;
    }

    private function labelHtml(string $label, string $description): string
    {
        $html = $this->wpService->escHtml($label);

        if ($description === '') {
            return $html;
        }

        return $html . '<span class="mod-guide-wizard__option-desc">' . $this->wpService->escHtml($description) . '</span>';
    }
}
