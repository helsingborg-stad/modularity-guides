<?php

declare(strict_types=1);

namespace ModularityGuides\Migration;

use WpService\Contracts\DeletePostMeta;
use WpService\Contracts\GetPostMeta;
use WpService\Contracts\GetPosts;
use WpService\Contracts\UpdatePostMeta;

/**
 * Migrates guides from the old accordion mode to the step by step mode.
 *
 * Existing content already works in the step by step mode (several answers per question,
 * outcome "continue"), so no texts are rewritten. The migration:
 * - sets "several answers" explicitly on every question, so the behaviour is kept even if defaults change,
 * - removes accordion-only data (display_mode, the "required" flag on checkboxes),
 * - moves steps without answer options to "Content" on the summary page tab (they were already shown there),
 * - reports what an editor should look at.
 *
 * plan() only reads; apply() saves a plan.
 */
class WizardMigration
{
    public const POST_TYPE = 'mod-guide';

    private GuideAnalyzer $analyzer;

    public function __construct(
        private GetPosts&GetPostMeta&UpdatePostMeta&DeletePostMeta $wpService,
    ) {
        $this->analyzer = new GuideAnalyzer($wpService);
    }

    /**
     * @return array<int, array{id: int, title: string, changes: array<int, array>, warnings: string[]}>
     */
    public function plan(): array
    {
        $guides = $this->wpService->getPosts([
            'post_type' => self::POST_TYPE,
            'post_status' => 'any',
            'numberposts' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        return array_map($this->analyzer->analyze(...), $guides);
    }

    /**
     * @param array<int, array{id: int, changes: array<int, array>}> $plans From plan()
     */
    public function apply(array $plans): void
    {
        foreach ($plans as $plan) {
            foreach ($plan['changes'] as $change) {
                $this->applyChange($plan['id'], $change);
            }
        }
    }

    private function applyChange(int $guideId, array $change): void
    {
        foreach ($change['update'] as $key => $value) {
            $this->wpService->updatePostMeta($guideId, $key, $value);
        }
        foreach ($change['delete'] as $key) {
            $this->wpService->deletePostMeta($guideId, $key);
        }
        if (isset($change['moveToSummary'])) {
            (new SummarySectionMove($this->wpService))->apply($guideId, $change['moveToSummary']);
        }
    }
}
