<?php

declare(strict_types=1);

namespace ModularityGuides\Helper;

/**
 * "Content" on the summary page tab (summary_sections) is added after the guide's steps as steps
 * without answer options. Steps without answer options are shown on the summary page, so the
 * view, "Show when", the todo list and the e-mail export handle them like before.
 */
class SummarySections
{
    public static function asSteps(array $fields): array
    {
        $sections = is_array($fields['summary_sections'] ?? null) ? $fields['summary_sections'] : [];
        unset($fields['summary_sections']);

        if ($sections === []) {
            return $fields;
        }
        // ACF returns false for a repeater without rows.
        $fields['steps'] = is_array($fields['steps'] ?? null) ? $fields['steps'] : [];

        foreach ($sections as $section) {
            $section = (array) $section;
            $content = is_array($section['content'] ?? null) ? $section['content'] : [];

            $fields['steps'][] = [
                'title' => (string) ($section['title'] ?? ''),
                'description' => (string) ($section['description'] ?? ''),
                // Answer options belong in questions, so they are never shown here.
                'content' => array_values(array_filter(
                    $content,
                    static fn($item): bool => (($item['acf_fc_layout'] ?? '') !== 'checkboxes'),
                )),
            ];
        }

        return $fields;
    }
}
