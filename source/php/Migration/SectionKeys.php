<?php

declare(strict_types=1);

namespace ModularityGuides\Migration;

/**
 * Meta keys of a step that moves to a summary page section, and the field keys they refer to.
 *
 * ACF stores a value as "steps_2_content_0_text" and the field it belongs to as
 * "_steps_2_content_0_text" => field key. The section has its own fields, so both are mapped.
 */
class SectionKeys
{
    /** Step sub fields that move, with the matching section field. */
    private const SECTION_FIELDS = [
        'title' => 'field_6912b1a0d0071',
        'description' => 'field_6912b1a0d0072',
        'show_advanced_fields' => 'field_6912b1a0d0074',
        'content' => 'field_6912b1a0d0100',
    ];

    /** Fields inside the step content and their copies in the section content. */
    private const CONTENT_FIELDS = [
        'field_57727e3d29818' => 'field_6912b1a0d0102',
        'field_6912b1a0d0060' => 'field_6912b1a0d0103',
        'field_57736cb2f03e9' => 'field_6912b1a0d0104',
        'field_577630ca269df' => 'field_6912b1a0d0106',
        'field_577630dd269e0' => 'field_6912b1a0d0107',
        'field_577630ea269e1' => 'field_6912b1a0d0108',
        'field_57763284d4d10' => 'field_6912b1a0d0109',
        'field_6912b1a0d0061' => 'field_6912b1a0d010a',
        'field_577370a3a7be7' => 'field_6912b1a0d010c',
        'field_577370aca7be8' => 'field_6912b1a0d010d',
        'field_577370b8a7be9' => 'field_6912b1a0d010e',
        'field_577370c3a7bea' => 'field_6912b1a0d010f',
        'field_6912b1a0d0062' => 'field_6912b1a0d0110',
        'field_577370cca7beb' => 'field_6912b1a0d0111',
        'field_57d653cf46a7d' => 'field_6912b1a0d0113',
        'field_57d653f646a7e' => 'field_6912b1a0d0114',
        'field_57d6546146a7f' => 'field_6912b1a0d0115',
        'field_6912b1a0d0063' => 'field_6912b1a0d0116',
        'field_57d6548046a80' => 'field_6912b1a0d0117',
    ];

    /**
     * The section's meta key, or null when the step field has no place in a section (e.g. icon).
     */
    public static function metaKey(string $key, int $step, int $section): ?string
    {
        [$reference, $rest] = self::split($key, $step);

        return self::field($rest) !== null ? "{$reference}summary_sections_{$section}_{$rest}" : null;
    }

    /**
     * The value to save: field keys of references are mapped, other values are kept.
     */
    public static function value(string $key, int $step, mixed $value): mixed
    {
        [$reference, $rest] = self::split($key, $step);
        if ($reference === '') {
            return $value;
        }
        $field = (string) self::field($rest);

        return $rest === $field ? self::SECTION_FIELDS[$field] : self::CONTENT_FIELDS[(string) $value] ?? $value;
    }

    /**
     * @return array{0: string, 1: string} ["_" for a field reference or "", key after "steps_N_"]
     */
    private static function split(string $key, int $step): array
    {
        $reference = str_starts_with($key, '_') ? '_' : '';

        return [$reference, substr($key, strlen("{$reference}steps_{$step}_"))];
    }

    private static function field(string $rest): ?string
    {
        foreach (array_keys(self::SECTION_FIELDS) as $field) {
            if ($rest === $field || str_starts_with($rest, $field . '_')) {
                return $field;
            }
        }

        return null;
    }
}
