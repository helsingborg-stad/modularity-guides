<?php

declare(strict_types=1);

namespace ModularityGuides\Migration;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

class WizardMigrationTest extends TestCase
{
    /**
     * @param array<string, mixed> $meta
     */
    private function migration(array $meta, FakeWpService &$wpService = null): WizardMigration
    {
        $post = (object) ['ID' => 7, 'post_title' => 'Guide'];
        $wpService = new FakeWpService([
            'getPosts' => [$post],
            'getPostMeta' => static fn(int $id, string $key): mixed => $meta[$key] ?? '',
            'updatePostMeta' => true,
            'deletePostMeta' => true,
        ]);

        return new WizardMigration($wpService);
    }

    private function questionGuide(): array
    {
        return [
            'steps' => 3,
            'steps_0_title' => 'Question',
            'steps_0_content' => ['text', 'checkboxes'],
            'steps_0_content_1_checkboxes' => 2,
            'steps_0_content_1_checkboxes_0_required' => '1',
            'steps_0_content_1_checkboxes_1_required' => '0',
            'steps_1_title' => 'Info in the middle',
            'steps_1_content' => ['text'],
            'steps_2_title' => 'Second question',
            'steps_2_content' => ['checkboxes'],
            'steps_2_content_0_checkboxes' => 1,
            'steps_2_selection_type' => 'single',
            'display_mode' => 'accordion',
        ];
    }

    public function testPlanReportsWithoutSaving(): void
    {
        $wpService = null;
        [$report] = $this->migration($this->questionGuide(), $wpService)->plan();
        $descriptions = array_column($report['changes'], 'description');

        static::assertSame(7, $report['id']);
        static::assertContains('Step 1: answers = several', $descriptions);
        static::assertNotContains('Step 3: answers = several', $descriptions, 'Existing values are kept');
        static::assertContains('Step 1: remove 2 unused "required" flag(s)', $descriptions);
        static::assertContains('Remove display_mode', $descriptions);
        static::assertCount(2, $report['warnings']);
        static::assertArrayNotHasKey('updatePostMeta', $wpService->methodCalls);
        static::assertArrayNotHasKey('deletePostMeta', $wpService->methodCalls);
    }

    public function testApplySavesDefaultsAndRemovesAccordionData(): void
    {
        $wpService = null;
        $migration = $this->migration($this->questionGuide(), $wpService);
        $migration->apply($migration->plan());

        static::assertContains([7, 'steps_0_selection_type', 'multiple'], array_map(
            static fn(array $call): array => array_slice($call, 0, 3),
            $wpService->methodCalls['updatePostMeta'],
        ));
        $deleted = array_column($wpService->methodCalls['deletePostMeta'], 1);
        static::assertContains('display_mode', $deleted);
        static::assertContains('steps_0_content_1_checkboxes_0_required', $deleted);
        static::assertContains('_steps_0_content_1_checkboxes_1_required', $deleted);
    }

    public function testWarnsWhenTheGuideHasNoQuestions(): void
    {
        [$report] = $this->migration(['steps' => 1, 'steps_0_content' => ['text']])->plan();

        static::assertSame(['No step has answer options: the content is shown without the step by step frame.'], $report['warnings']);
    }

    public function testWarnsWhenTheGuideHasNoSteps(): void
    {
        [$report] = $this->migration([])->plan();

        static::assertSame(['The guide has no steps.'], $report['warnings']);
        static::assertSame([], $report['changes']);
    }
}
