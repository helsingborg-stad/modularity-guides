<?php

declare(strict_types=1);

namespace ModularityGuides\Admin;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

class AnswerFieldsTest extends TestCase
{
    private function answerFields(array $meta = []): AnswerFields
    {
        return new AnswerFields(new FakeWpService([
            'getPostMeta' => static fn(int $id, string $key): mixed => $meta[$key] ?? '',
            'getTheID' => 5,
            'addFilter' => true,
            'addAction' => true,
        ]));
    }

    private function guideMeta(): array
    {
        return [
            'steps' => 2,
            'steps_0_title' => 'What is your situation?',
            'steps_0_short_title' => 'Situation',
            'steps_0_content' => ['text', 'checkboxes'],
            'steps_0_content_1_checkboxes' => 2,
            'steps_0_content_1_checkboxes_0_key' => 'money',
            'steps_0_content_1_checkboxes_0_label' => 'No money',
            'steps_0_content_1_checkboxes_1_key' => '',
            'steps_0_content_1_checkboxes_1_label' => 'Without key',
            'steps_1_title' => 'Debts',
            'steps_1_content' => ['checkboxes'],
            'steps_1_content_0_checkboxes' => 1,
            'steps_1_content_0_checkboxes_0_key' => 'debt',
            'steps_1_content_0_checkboxes_0_label' => 'I have debts',
        ];
    }

    public function testAnswerChoicesListKeyedAnswersWithTheirQuestion(): void
    {
        static::assertSame(
            ['money' => 'Situation: No money', 'debt' => 'Debts: I have debts'],
            (new AnswerChoices(new FakeWpService(['getPostMeta' => fn(int $id, string $key): mixed => $this->guideMeta()[$key] ?? ''])))->forGuide(5),
        );
    }

    public function testChoicesKeepSavedValuesThatNoLongerMatchAnAnswer(): void
    {
        $field = $this->answerFields($this->guideMeta())->addAnswerChoices(['value' => ['debt', 'removed-answer']]);

        static::assertSame('Debts: I have debts', $field['choices']['debt']);
        static::assertSame('removed-answer', $field['choices']['removed-answer']);
    }

    public function testHiddenFieldsArePassedThrough(): void
    {
        static::assertFalse($this->answerFields()->addAnswerChoices(false));
    }

    public function testEmptyAnswerKeysAreGenerated(): void
    {
        $fields = $this->answerFields();

        static::assertSame('money', $fields->ensureAnswerKey(' money '));
        static::assertMatchesRegularExpression('/^answer-[0-9a-f]{8}$/', $fields->ensureAnswerKey(''));
        static::assertNotSame($fields->ensureAnswerKey(''), $fields->ensureAnswerKey(''));
    }
}
