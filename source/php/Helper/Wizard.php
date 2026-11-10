<?php

declare(strict_types=1);

namespace ModularityGuides\Helper;

/**
 * Prepares guide fields for the step by step (wizard) display mode.
 *
 * Steps containing at least one "checkboxes" layout become questions.
 * Steps without answer options (e.g. a todo list) are shown on the summary page.
 */
class Wizard
{
    public const OUTCOMES = ['pass', 'stop', 'review'];

    public static function build(array $fields, array $lang): array
    {
        $questions = [];
        $summarySteps = [];

        foreach ($fields['steps'] ?? [] as $stepIndex => $step) {
            $contents = is_array($step['content'] ?? null) ? $step['content'] : [];
            $hasOptions = false;
            $hasOutcomes = false;
            $optionIndex = 0;
            $type = ($step['selection_type'] ?? 'multiple') === 'single' ? 'radio' : 'checkbox';

            foreach ($contents as &$content) {
                if (($content['acf_fc_layout'] ?? '') !== 'checkboxes') {
                    continue;
                }

                $options = [];
                foreach ($content['checkboxes'] ?? [] as $checkbox) {
                    $outcome = in_array($checkbox['outcome'] ?? 'pass', self::OUTCOMES, true)
                        ? $checkbox['outcome'] ?? 'pass'
                        : 'pass';
                    $key = trim((string) ($checkbox['key'] ?? ''));

                    $options[] = [
                        'id' => 'mod-guide-' . $stepIndex . '-' . $optionIndex,
                        'value' => $key !== '' ? $key : 'option-' . $stepIndex . '-' . $optionIndex,
                        'toggleKey' => $key,
                        'relation' => $type === 'checkbox' ? trim((string) ($checkbox['relate_to'] ?? '')) : '',
                        'label' => (string) ($checkbox['label'] ?? ''),
                        'description' => (string) ($checkbox['description'] ?? ''),
                        'outcome' => $outcome,
                        'reason' => (string) ($checkbox['outcome_reason'] ?? ''),
                    ];
                    $hasOptions = true;
                    $hasOutcomes = $hasOutcomes || $outcome !== 'pass';
                    $optionIndex++;
                }
                $content['wizard_options'] = $options;
            }
            unset($content);

            $title = (string) ($step['title'] ?? '');
            $prepared = [
                'title' => $title,
                'shortTitle' => trim((string) ($step['short_title'] ?? '')) ?: $title,
                'description' => (string) ($step['description'] ?? ''),
                'help' => (string) ($step['help'] ?? ''),
                'icon' => trim((string) ($step['icon'] ?? '')),
                'inputType' => $type,
                'showTags' => $hasOutcomes,
                'content' => $contents,
            ];

            if ($hasOptions) {
                $questions[] = $prepared;
            } else {
                $summarySteps[] = $prepared;
            }
        }

        $summary = is_array($fields['summary_page'] ?? null) ? $fields['summary_page'] : [];
        $text = static fn(string $key, string $fallback): string => trim((string) ($summary[$key] ?? '')) ?: $fallback;

        return [
            'questions' => $questions,
            'summarySteps' => $summarySteps,
            'results' => [
                'pass' => [
                    'title' => $text('pass_title', $lang['wizard_pass_title']),
                    'text' => $text('pass_text', $lang['wizard_pass_text']),
                ],
                'stop' => [
                    'title' => $text('stop_title', $lang['wizard_stop_title']),
                    'text' => $text('stop_text', $lang['wizard_stop_text']),
                ],
                'review' => [
                    'title' => $text('review_title', $lang['wizard_review_title']),
                    'text' => $text('review_text', $lang['wizard_review_text']),
                ],
            ],
            'notice' => trim((string) ($summary['notice'] ?? '')),
        ];
    }
}
