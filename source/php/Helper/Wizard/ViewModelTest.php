<?php

declare(strict_types=1);

namespace ModularityGuides\Helper\Wizard;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

class ViewModelTest extends TestCase
{
    private function create(array $fields): array
    {
        $viewModel = new ViewModel(new FakeWpService([
            'escHtml' => static fn(string $text): string => $text,
            'wpStripAllTags' => strip_tags(...),
            'translateNoopedPlural' => static fn(array $noop): string => $noop['plural'],
        ]));

        $lang = [
            'wizard_question_x_of_y' => 'Question %1$s of %2$s',
            'wizard_show_summary' => 'Show summary',
            'wizard_continue' => 'Continue',
            'wizard_stopped_at' => '%s',
            'wizard_answered' => ['singular' => '%s', 'plural' => '%s'],
            'wizard_remaining' => ['singular' => '%s', 'plural' => '%s'],
        ];
        foreach (['pass', 'stop', 'review'] as $type) {
            $lang['wizard_' . $type . '_title'] = 'Default ' . $type . ' title';
            $lang['wizard_' . $type . '_text'] = 'Default ' . $type . ' text';
        }
        foreach (['summary', 'choose_one', 'choose_many', 'saved_answer', 'note_pass', 'note_stop', 'note_review',
            'your_answers', 'edit', 'not_answered', 'outcome_stop', 'outcome_review', 'created', 'download_done'] as $key) {
            $lang['wizard_' . $key] = $key;
        }

        return $viewModel->create($fields, $lang, 12, '<em>Guide</em>');
    }

    private function question(string $title, string $description = ''): array
    {
        return [
            'title' => $title,
            'description' => $description,
            'content' => [['acf_fc_layout' => 'checkboxes', 'checkboxes' => [['label' => 'A']]]],
        ];
    }

    public function testSplitsQuestionsAndSummaryContent(): void
    {
        $result = $this->create(['steps' => [
            $this->question('First', 'Hint'),
            ['title' => 'Todo', 'content' => [['acf_fc_layout' => 'todo']]],
            $this->question('Second'),
        ]]);

        static::assertSame('mod-guide-wizard-12', $result['id']);
        static::assertSame('Guide', $result['plainTitle']);
        static::assertSame(['First', 'Second'], array_column($result['questions'], 'title'));
        static::assertSame(['Todo'], array_column($result['summarySteps'], 'title'));
    }

    public function testAddsPositionDependentPresentation(): void
    {
        [$first, $second] = $this->create(['steps' => [$this->question('First', 'Hint'), $this->question('Second')]])['questions'];

        static::assertSame('Question 1 of 2 · First', $first['eyebrow']);
        static::assertSame('Continue', $first['nextLabel']);
        static::assertSame('Show summary', $second['nextLabel']);
        static::assertSame('mod-guide-wizard-12-q0-hint', $first['hintId']);
        static::assertSame('', $second['hintId']);
        static::assertArrayNotHasKey('hidden', $first['attributes']);
        static::assertSame('hidden', $second['attributes']['hidden']);
    }

    public function testResultsUseSummaryPageTextsWithDefaultFallback(): void
    {
        $result = $this->create(['steps' => [], 'summary_page' => ['stop_title' => 'Custom stop', 'notice' => ' Notice ']]);
        $results = array_column($result['results'], null, 'type');

        static::assertSame('Custom stop', $results['stop']['title']);
        static::assertSame('danger', $results['stop']['noticeType']);
        static::assertSame('Default pass title', $results['pass']['title']);
        static::assertStringContainsString('Default review text', $results['review']['messageHtml']);
        static::assertSame('Notice', $result['notice']);
    }

    public function testHandlesMissingSteps(): void
    {
        $result = $this->create([]);

        static::assertSame([], $result['questions']);
        static::assertSame([], $result['summarySteps']);
        static::assertSame(['0'], $result['script']['answered']);
    }
}
