<?php

declare(strict_types=1);

namespace ModularityGuides\Helper\Wizard;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

class OptionFactoryTest extends TestCase
{
    private function factory(): OptionFactory
    {
        return new OptionFactory(new FakeWpService([
            'escHtml' => static fn(string $text): string => htmlspecialchars($text, ENT_QUOTES),
        ]));
    }

    public function testCreatesOptionWithToggleKeyAndOutcome(): void
    {
        $option = $this->factory()->create(
            ['key' => 'large', 'label' => 'Large', 'outcome' => 'stop', 'outcome_reason' => 'Too big'],
            'radio',
            'guide-0-1',
            'guide-q0',
            'option-0-1',
        );

        static::assertSame('large', $option['value']);
        static::assertSame('stop', $option['outcome']);
        static::assertSame('large', $option['attributes']['data-mod-guide-toggle-key']);
        static::assertSame('stop', $option['attributes']['data-js-guide-wizard-outcome']);
        static::assertSame('Too big', $option['attributes']['data-js-guide-wizard-reason']);
    }

    public function testFallsBackToGeneratedValueWithoutToggleKey(): void
    {
        $option = $this->factory()->create(['label' => 'A'], 'checkbox', 'id', 'name', 'option-2-3');

        static::assertSame('option-2-3', $option['value']);
        static::assertArrayNotHasKey('data-mod-guide-toggle-key', $option['attributes']);
    }

    public function testEscapesLabelAndAddsDescription(): void
    {
        $option = $this->factory()->create(
            ['label' => 'Small <b>one</b>', 'description' => 'Up to 30 m²'],
            'radio',
            'id',
            'name',
            'value',
        );

        static::assertSame(
            'Small &lt;b&gt;one&lt;/b&gt;<span class="mod-guide-wizard__option-desc">Up to 30 m²</span>',
            $option['labelHtml'],
        );
    }

    public function testRelationsOnlyApplyToCheckboxes(): void
    {
        $row = ['key' => 'a', 'label' => 'A', 'relate_to' => 'b'];

        $checkbox = $this->factory()->create($row, 'checkbox', 'id', 'name', 'value');
        $radio = $this->factory()->create($row, 'radio', 'id', 'name', 'value');

        static::assertSame('b', $checkbox['attributes']['data-mod-guide-relation']);
        static::assertArrayNotHasKey('data-mod-guide-relation', $radio['attributes']);
    }

    public function testUnknownOutcomeFallsBackToPass(): void
    {
        static::assertSame('pass', OptionFactory::normalizeOutcome('invalid'));
        static::assertSame('pass', OptionFactory::normalizeOutcome(null));
        static::assertSame('review', OptionFactory::normalizeOutcome('review'));
    }
}
