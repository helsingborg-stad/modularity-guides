<?php

declare(strict_types=1);

namespace ModularityGuides\Helper\Wizard;

use WpService\Contracts\TranslateNoopedPlural;

/**
 * Texts for the script (source/js/Wizard.ts).
 *
 * Texts containing numbers are prepared for every possible value, so plural forms come from
 * WordPress (_n_noop() in Lang) and the script never has to format or pluralise strings.
 */
class ScriptStrings
{
    private const TEXT_DOMAIN = 'modularity-guides';

    private const STATIC_KEYS = [
        'summary' => 'wizard_summary',
        'chooseOne' => 'wizard_choose_one',
        'chooseMany' => 'wizard_choose_many',
        'savedAnswer' => 'wizard_saved_answer',
        'notePass' => 'wizard_note_pass',
        'noteStop' => 'wizard_note_stop',
        'noteReview' => 'wizard_note_review',
        'yourAnswers' => 'wizard_your_answers',
        'edit' => 'wizard_edit',
        'notAnswered' => 'wizard_not_answered',
        'outcomeStop' => 'wizard_outcome_stop',
        'outcomeReview' => 'wizard_outcome_review',
        'created' => 'wizard_created',
        'downloadDone' => 'wizard_download_done',
        'noteChosen' => 'wizard_note_chosen',
    ];

    public function __construct(
        private TranslateNoopedPlural $wpService,
    ) {}

    /**
     * @param array $questions Prepared questions (with position and shortTitle)
     */
    public function create(array $questions, array $lang): array
    {
        $counts = range(0, count($questions));

        return [
            'answered' => array_map(fn(int $count): string => $this->plural($lang['wizard_answered'], $count), $counts),
            'remaining' => array_map(fn(int $count): string => $this->plural($lang['wizard_remaining'], $count), $counts),
            'position' => array_column($questions, 'position'),
            'stoppedAt' => array_map(
                static fn(array $question): string => sprintf($lang['wizard_stopped_at'], $question['shortTitle']),
                $questions,
            ),
            ...array_map(static fn(string $key): string => $lang[$key], self::STATIC_KEYS),
        ];
    }

    private function plural(array $noopedPlural, int $count): string
    {
        return sprintf($this->wpService->translateNoopedPlural($noopedPlural, $count, self::TEXT_DOMAIN), $count);
    }
}
