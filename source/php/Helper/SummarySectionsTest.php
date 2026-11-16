<?php

declare(strict_types=1);

namespace ModularityGuides\Helper;

use PHPUnit\Framework\TestCase;

class SummarySectionsTest extends TestCase
{
    public function testSectionsAreAddedAfterTheStepsWithoutAnswers(): void
    {
        $fields = SummarySections::asSteps([
            'steps' => [['title' => 'Question', 'content' => [['acf_fc_layout' => 'checkboxes']]]],
            'summary_sections' => [[
                'title' => 'Before you continue',
                'description' => 'Check this',
                'content' => [['acf_fc_layout' => 'text'], ['acf_fc_layout' => 'checkboxes']],
            ]],
        ]);

        static::assertCount(2, $fields['steps']);
        static::assertSame('Before you continue', $fields['steps'][1]['title']);
        static::assertSame('Check this', $fields['steps'][1]['description']);
        static::assertSame([['acf_fc_layout' => 'text']], $fields['steps'][1]['content']);
        static::assertArrayNotHasKey('summary_sections', $fields);
    }

    public function testFieldsWithoutSectionsKeepTheirSteps(): void
    {
        static::assertSame(['steps' => [['title' => 'Q']]], SummarySections::asSteps(['steps' => [['title' => 'Q']], 'summary_sections' => false]));
    }

    public function testSectionsWorkWhenTheGuideHasNoSteps(): void
    {
        $fields = SummarySections::asSteps(['steps' => false, 'summary_sections' => [['title' => 'Todo', 'content' => false]]]);

        static::assertSame([['title' => 'Todo', 'description' => '', 'content' => []]], $fields['steps']);
    }
}
