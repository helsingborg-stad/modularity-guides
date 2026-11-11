<?php

declare(strict_types=1);

namespace ModularityGuides\Migration;

use WpService\Contracts\DeletePostMeta;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\UpdatePostMeta;

/**
 * Moves steps without answer options to "Content" on the summary page tab (summary_sections).
 *
 * Those steps were already shown on the summary page, so the guide looks the same; editors now
 * find the content where it is shown. The remaining steps are renumbered.
 *
 * The meta is read when the move is applied (not when it is planned), so changes to the
 * question steps earlier in the same migration are kept.
 */
class SummarySectionMove
{
    public const SECTIONS_FIELD = 'field_6912b1a0d0070';

    public function __construct(
        private GetPostMeta&UpdatePostMeta&DeletePostMeta $wpService,
    ) {}

    /**
     * @param int[] $steps Indexes of the steps to move, in order
     */
    public function apply(int $guideId, array $steps): void
    {
        $stepCount = (int) $this->meta($guideId, 'steps');
        $sectionCount = (int) $this->meta($guideId, 'summary_sections');
        $keys = array_keys((array) $this->wpService->getPostMeta($guideId));
        $updates = [];
        $oldKeys = [];
        $newIndex = 0;

        for ($step = 0; $step < $stepCount; $step++) {
            $moves = in_array($step, $steps, true);
            // Steps before the first moved step keep their index and meta.
            if (!$moves && $step === $newIndex) {
                $newIndex++;
                continue;
            }
            $stepKeys = $this->keysWithPrefix($keys, "steps_{$step}_");
            array_push($oldKeys, ...$stepKeys);

            $updates += $moves
                ? $this->toSection($guideId, $stepKeys, $step, $sectionCount++)
                : $this->renumber($guideId, $stepKeys, $step, $newIndex++);
        }

        $updates += [
            'steps' => $newIndex,
            'summary_sections' => $sectionCount,
            '_summary_sections' => self::SECTIONS_FIELD,
        ];

        foreach ($updates as $key => $value) {
            $this->wpService->updatePostMeta($guideId, $key, $value);
        }
        foreach (array_diff($oldKeys, array_keys($updates)) as $key) {
            $this->wpService->deletePostMeta($guideId, $key);
        }
    }

    /**
     * @param string[] $stepKeys
     * @return array<string, mixed>
     */
    private function toSection(int $guideId, array $stepKeys, int $step, int $section): array
    {
        $updates = [];

        foreach ($stepKeys as $key) {
            $newKey = SectionKeys::metaKey($key, $step, $section);
            if ($newKey !== null) {
                $updates[$newKey] = SectionKeys::value($key, $step, $this->meta($guideId, $key));
            }
        }

        return $updates;
    }

    /**
     * @param string[] $stepKeys
     * @return array<string, mixed>
     */
    private function renumber(int $guideId, array $stepKeys, int $step, int $newIndex): array
    {
        $updates = [];

        foreach ($stepKeys as $key) {
            $newKey = preg_replace('/^(_?)steps_' . $step . '_/', '${1}steps_' . $newIndex . '_', $key);
            $updates[(string) $newKey] = $this->meta($guideId, $key);
        }

        return $updates;
    }

    /**
     * @param array<int|string> $keys
     * @return string[]
     */
    private function keysWithPrefix(array $keys, string $prefix): array
    {
        return array_values(array_filter(
            array_map('strval', $keys),
            static fn(string $key): bool => str_starts_with($key, $prefix) || str_starts_with($key, '_' . $prefix),
        ));
    }

    private function meta(int $postId, string $key): mixed
    {
        return $this->wpService->getPostMeta($postId, $key, true);
    }
}
