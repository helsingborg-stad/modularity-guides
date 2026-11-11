<?php

declare(strict_types=1);

namespace ModularityGuides\Helper\Wizard;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;

class ScriptStringsTest extends TestCase
{
    public function testPreparesPluralFormsForEveryCount(): void
    {
        $scriptStrings = new ScriptStrings(new FakeWpService([
            'translateNoopedPlural' => static fn(array $noop, int $count): string => $count === 1 ? $noop['singular'] : $noop['plural'],
        ]));
        $lang = array_fill_keys([
            'wizard_summary', 'wizard_choose_one', 'wizard_choose_many', 'wizard_saved_answer', 'wizard_note_pass',
            'wizard_note_stop', 'wizard_note_review', 'wizard_your_answers', 'wizard_edit', 'wizard_not_answered',
            'wizard_outcome_stop', 'wizard_outcome_review', 'wizard_created', 'wizard_download_done', 'wizard_note_chosen',
        ], 'text');
        $lang['wizard_answered'] = ['singular' => '%s answered', 'plural' => '%s answered (many)'];
        $lang['wizard_remaining'] = ['singular' => '%s remains', 'plural' => '%s remain'];
        $lang['wizard_stopped_at'] = 'Stopped at "%s".';

        $strings = $scriptStrings->create([
            ['position' => 'Question 1 of 2', 'shortTitle' => 'Size'],
            ['position' => 'Question 2 of 2', 'shortTitle' => 'Height'],
        ], $lang);

        static::assertSame(['0 answered (many)', '1 answered', '2 answered (many)'], $strings['answered']);
        static::assertSame(['0 remain', '1 remains', '2 remain'], $strings['remaining']);
        static::assertSame(['Question 1 of 2', 'Question 2 of 2'], $strings['position']);
        static::assertSame(['Stopped at "Size".', 'Stopped at "Height".'], $strings['stoppedAt']);
        static::assertSame('text', $strings['chooseOne']);
    }
}
