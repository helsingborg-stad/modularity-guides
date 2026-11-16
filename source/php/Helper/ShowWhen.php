<?php

declare(strict_types=1);

namespace ModularityGuides\Helper;

/**
 * "Show when" lets editors pick answers instead of typing toggle keys.
 *
 * Content (tips, modal text, todo items, related guides) stores the chosen answer keys in
 * `show_when`. Before rendering, they are written to `toggle_key` as an OR list ("a,b"), so the
 * existing toggle engine and the e-mail export keep working. A toggle key typed by hand (advanced
 * fields) wins over "Show when".
 */
class ShowWhen
{
    /** Repeaters inside a layout whose rows can have their own "show when". */
    private const ROW_REPEATERS = ['todo' => 'list_items', 'related_guides' => 'guides'];

    public static function apply(array $fields): array
    {
        foreach ($fields['steps'] ?? [] as $stepIndex => $step) {
            foreach ($step['content'] ?? [] as $contentIndex => $content) {
                $fields['steps'][$stepIndex]['content'][$contentIndex] = self::applyToContent((array) $content);
            }
        }

        return $fields;
    }

    private static function applyToContent(array $content): array
    {
        $content = self::toToggleKey($content);
        $repeater = self::ROW_REPEATERS[$content['acf_fc_layout'] ?? ''] ?? null;

        if ($repeater !== null && is_array($content[$repeater] ?? null)) {
            $content[$repeater] = array_map(
                static fn($row): mixed => is_array($row) ? self::toToggleKey($row) : $row,
                $content[$repeater],
            );
        }

        return $content;
    }

    private static function toToggleKey(array $item): array
    {
        $keys = array_filter(array_map('strval', (array) ($item['show_when'] ?? [])), static fn(string $key): bool => $key !== '');

        if (trim((string) ($item['toggle_key'] ?? '')) === '' && $keys !== []) {
            $item['toggle_key'] = implode(',', $keys);
        }

        return $item;
    }
}
