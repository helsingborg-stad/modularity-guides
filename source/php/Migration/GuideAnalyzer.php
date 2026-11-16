<?php

declare(strict_types=1);

namespace ModularityGuides\Migration;

use WpService\Contracts\GetPostMeta;

/**
 * Works out what the migration to the step by step mode changes for one guide.
 *
 * A change is ['description' => string, 'update' => [metaKey => value], 'delete' => [metaKey, ...]],
 * optionally with 'moveToSummary' => [step index, ...] (see SummarySectionMove).
 */
class GuideAnalyzer
{
    private const SELECTION_TYPE_FIELD_KEY = 'field_6912b1a0d0013';

    public function __construct(
        private GetPostMeta $wpService,
    ) {}

    /**
     * @param object $guide WP_Post (only ID and post_title are used)
     * @return array{id: int, title: string, changes: array<int, array>, warnings: string[]}
     */
    public function analyze(object $guide): array
    {
        $guideId = (int) $guide->ID;
        $stepCount = (int) $this->meta($guideId, 'steps');
        $changes = [];
        $warnings = [];
        $questionSteps = [];

        for ($step = 0; $step < $stepCount; $step++) {
            $prefixes = $this->checkboxPrefixes($guideId, $step);
            if ($prefixes === []) {
                continue;
            }
            $questionSteps[] = $step;
            array_push($changes, ...$this->selectionTypeChanges($guideId, $step));

            foreach ($prefixes as $prefix => $rows) {
                [$change, $warning] = $this->requiredFlagChanges($guideId, $prefix, $rows, $step);
                array_push($changes, ...$change);
                array_push($warnings, ...$warning);
            }
        }

        if ($this->meta($guideId, 'display_mode') !== '') {
            $changes[] = ['description' => 'Remove display_mode', 'update' => [], 'delete' => ['display_mode', '_display_mode']];
        }

        // Last, as it renumbers the steps.
        array_push($changes, ...$this->summaryMoveChanges($guideId, $stepCount, $questionSteps));

        return [
            'id' => $guideId,
            'title' => (string) $guide->post_title,
            'changes' => $changes,
            'warnings' => [...$warnings, ...(new StructureWarnings())->for($stepCount, $questionSteps)],
        ];
    }

    /**
     * Steps without answer options in a guide with questions move to the summary page content.
     * The change is applied last, as it renumbers the steps.
     *
     * @param int[] $questionSteps
     */
    private function summaryMoveChanges(int $guideId, int $stepCount, array $questionSteps): array
    {
        if ($questionSteps === []) {
            return [];
        }
        $steps = array_values(array_diff(range(0, $stepCount - 1), $questionSteps));

        return $steps === [] ? [] : [[
            'description' => sprintf(
                'Move %s to the summary page content',
                implode(', ', array_map(
                    fn(int $step): string => sprintf('step %d "%s"', $step + 1, (string) $this->meta($guideId, "steps_{$step}_title")),
                    $steps,
                )),
            ),
            'update' => [],
            'delete' => [],
            'moveToSummary' => $steps,
        ]];
    }

    /**
     * Meta key prefixes of the step's checkboxes layouts that have rows, with their row count.
     *
     * @return array<string, int>
     */
    private function checkboxPrefixes(int $guideId, int $step): array
    {
        $prefixes = [];

        foreach ((array) $this->meta($guideId, "steps_{$step}_content") as $index => $layout) {
            $prefix = "steps_{$step}_content_{$index}_checkboxes";
            $rows = (int) $this->meta($guideId, $prefix);
            if ($layout === 'checkboxes' && $rows > 0) {
                $prefixes[$prefix] = $rows;
            }
        }

        return $prefixes;
    }

    private function selectionTypeChanges(int $guideId, int $step): array
    {
        $key = "steps_{$step}_selection_type";

        if ($this->meta($guideId, $key) !== '') {
            return [];
        }

        return [[
            'description' => sprintf('Step %d: answers = several', $step + 1),
            'update' => [$key => 'multiple', '_' . $key => self::SELECTION_TYPE_FIELD_KEY],
            'delete' => [],
        ]];
    }

    /**
     * @return array{0: array<int, array>, 1: string[]}
     */
    private function requiredFlagChanges(int $guideId, string $prefix, int $rows, int $step): array
    {
        $keys = [];
        $required = 0;

        for ($row = 0; $row < $rows; $row++) {
            $key = "{$prefix}_{$row}_required";
            $value = $this->meta($guideId, $key);
            if ($value === '') {
                continue;
            }
            $required += (int) $value;
            array_push($keys, $key, '_' . $key);
        }

        if ($keys === []) {
            return [[], []];
        }

        $change = [
            'description' => sprintf('Step %d: remove %d unused "required" flag(s)', $step + 1, count($keys) / 2),
            'update' => [],
            'delete' => $keys,
        ];
        $warnings = $required > 0
            ? [sprintf(
                'Step %d: %d checkbox(es) were required; this is no longer enforced, the visitor must choose at least one answer.',
                $step + 1,
                $required,
            )]
            : [];

        return [[$change], $warnings];
    }

    private function meta(int $postId, string $key): mixed
    {
        return $this->wpService->getPostMeta($postId, $key, true);
    }
}
