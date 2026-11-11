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
            'getPostMeta' => static fn(int $id, string $key = ''): mixed => $key === '' ? array_map(static fn($value): array => [$value], $meta) : $meta[$key] ?? '',
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
        static::assertContains('Move step 2 "Info in the middle" to the summary page content', $descriptions);
        static::assertSame('Move step 2 "Info in the middle" to the summary page content', end($descriptions), 'The move renumbers steps, so it comes last');
        static::assertCount(1, $report['warnings']);
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

    public function testApplyMovesStepsWithoutAnswersToTheSummaryPage(): void
    {
        $meta = [
            'steps' => 3,
            '_steps' => 'field_57727933e3560',
            'steps_0_title' => 'Info first',
            '_steps_0_title' => 'field_577279cfb8810',
            'steps_0_icon' => 'info',
            'steps_0_content' => ['text'],
            '_steps_0_content' => 'field_57727e2129817',
            'steps_0_content_0_text' => 'Hello',
            '_steps_0_content_0_text' => 'field_57727e3d29818',
            'steps_1_title' => 'Question',
            'steps_1_content' => ['checkboxes'],
            'steps_1_content_0_checkboxes' => 1,
            'steps_2_title' => 'Before you continue',
            'steps_2_content' => ['todo'],
            '_steps_2_content' => 'field_57727e2129817',
            'steps_2_content_0_list_items' => 1,
            'steps_2_content_0_list_items_0_title' => 'Group',
            '_steps_2_content_0_list_items_0_title' => 'field_577370aca7be8',
        ];
        $wpService = new FakeWpService([
            'getPosts' => [(object) ['ID' => 7, 'post_title' => 'Guide']],
            'getPostMeta' => static function (int $id, string $key = '') use (&$meta): mixed {
                return $key === '' ? array_map(static fn($value): array => [$value], $meta) : $meta[$key] ?? '';
            },
            'updatePostMeta' => static function (int $id, string $key, mixed $value) use (&$meta): bool {
                $meta[$key] = $value;
                return true;
            },
            'deletePostMeta' => static function (int $id, string $key) use (&$meta): bool {
                unset($meta[$key]);
                return true;
            },
        ]);
        $migration = new WizardMigration($wpService);
        $migration->apply($migration->plan());

        static::assertSame(1, $meta['steps']);
        static::assertSame('Question', $meta['steps_0_title']);
        static::assertSame('multiple', $meta['steps_0_selection_type'], 'Earlier changes use the old index and are moved along');
        static::assertSame(1, $meta['steps_0_content_0_checkboxes']);
        static::assertSame(2, $meta['summary_sections']);
        static::assertSame('field_6912b1a0d0070', $meta['_summary_sections']);
        static::assertSame('Info first', $meta['summary_sections_0_title']);
        static::assertSame('field_6912b1a0d0071', $meta['_summary_sections_0_title']);
        static::assertSame('Hello', $meta['summary_sections_0_content_0_text']);
        static::assertSame('field_6912b1a0d0102', $meta['_summary_sections_0_content_0_text']);
        static::assertSame('field_6912b1a0d0100', $meta['_summary_sections_0_content']);
        static::assertSame('Group', $meta['summary_sections_1_content_0_list_items_0_title']);
        static::assertSame('field_6912b1a0d010d', $meta['_summary_sections_1_content_0_list_items_0_title']);
        static::assertArrayNotHasKey('summary_sections_0_icon', $meta, 'Question-only fields are not moved');
        static::assertSame([], array_values(array_filter(
            array_keys($meta),
            static fn(string $key): bool => (bool) preg_match('/^_?steps_[12]_/', $key),
        )));
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
