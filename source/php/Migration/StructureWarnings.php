<?php

declare(strict_types=1);

namespace ModularityGuides\Migration;

/**
 * Warnings about a guide's structure that an editor should look at after the migration.
 */
class StructureWarnings
{
    /**
     * @param int[] $questionSteps
     * @return string[]
     */
    public function for(int $stepCount, array $questionSteps): array
    {
        if ($stepCount === 0) {
            return ['The guide has no steps.'];
        }

        if ($questionSteps === []) {
            return ['No step has answer options: the content is shown without the step by step frame.'];
        }

        return [];
    }
}
