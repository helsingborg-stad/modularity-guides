<?php

declare(strict_types=1);

namespace ModularityGuides\Helper\Wizard;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

class StepFactoryTest extends TestCase
{
    private function factory(): StepFactory
    {
        return new StepFactory(new OptionFactory(new FakeWpService([
            'escHtml' => static fn(string $text): string => $text,
        ])));
    }

    public function testStepWithOptionsIsAQuestion(): void
    {
        $step = $this->factory()->create([
            'title' => 'How big?',
            'short_title' => 'Size',
            'selection_type' => 'single',
            'content' => [
                ['acf_fc_layout' => 'text', 'text' => 'Tip'],
                ['acf_fc_layout' => 'checkboxes', 'checkboxes' => [['key' => 'a', 'label' => 'A']]],
            ],
        ], 0, 'guide');

        static::assertTrue($step['hasOptions']);
        static::assertSame('radio', $step['inputType']);
        static::assertSame('Size', $step['shortTitle']);
        static::assertArrayNotHasKey('wizard_options', $step['content'][0]);
        static::assertSame('guide-q0', $step['content'][1]['wizard_options'][0]['name']);
        static::assertSame('guide-0-0', $step['content'][0]['stepId'], 'Partials get a unique id per step and content');
        static::assertSame('guide-0-1', $step['content'][1]['stepId']);
    }

    public function testStepWithoutOptionsIsSummaryContent(): void
    {
        $step = $this->factory()->create([
            'title' => 'Todo',
            'content' => [['acf_fc_layout' => 'todo', 'list_items' => []]],
        ], 3, 'guide');

        static::assertFalse($step['hasOptions']);
        static::assertSame('checkbox', $step['inputType'], 'Several answers is the default');
        static::assertSame('Todo', $step['shortTitle'], 'Short title falls back to the title');
    }

    public function testTagsAreOnlyShownWhenAnAnswerCanEndTheGuide(): void
    {
        $passOnly = $this->factory()->create(['content' => [
            ['acf_fc_layout' => 'checkboxes', 'checkboxes' => [['label' => 'A'], ['label' => 'B']]],
        ]], 0, 'guide');
        $withStop = $this->factory()->create(['content' => [
            ['acf_fc_layout' => 'checkboxes', 'checkboxes' => [['label' => 'A'], ['label' => 'B', 'outcome' => 'stop']]],
        ]], 0, 'guide');

        static::assertFalse($passOnly['showTags']);
        static::assertTrue($withStop['showTags']);
    }
}
