<?php

declare(strict_types=1);

namespace ModularityGuides\Migration;

use WpService\Contracts\GetPostMeta;

/**
 * Warnings about a guide's structure that an editor should look at after the migration.
 */
class StructureWarnings
{
    public function __construct(
        private GetPostMeta $wpService,
    ) {}

    /**
     * @param int[] $questionSteps
     * @return string[]
     */
    public function for(int $guideId, int $stepCount, array $questionSteps): array
    {
        if ($stepCount === 0) {
            return ['The guide has no steps.'];
        }

        if ($questionSteps === []) {
            return ['No step has answer options: the content is shown without the step by step frame.'];
        }

        $warnings = [];
        for ($step = 0; $step < max($questionSteps); $step++) {
            if (in_array($step, $questionSteps, true)) {
                continue;
            }
            $warnings[] = sprintf(
                'Step %d "%s" has no answer options; it moves to the summary page.',
                $step + 1,
                (string) $this->meta($guideId, "steps_{$step}_title"),
            );
        }

        return $warnings;
    }

    private function meta(int $postId, string $key): mixed
    {
        return $this->wpService->getPostMeta($postId, $key, true);
    }
}
