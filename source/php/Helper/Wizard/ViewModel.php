<?php

declare(strict_types=1);

namespace ModularityGuides\Helper\Wizard;

use WpService\Contracts\EscHtml;
use WpService\Contracts\TranslateNoopedPlural;
use WpService\Contracts\WpStripAllTags;

/**
 * View model for the step by step (wizard) display mode.
 *
 * Steps with answer options become questions; steps without (e.g. a todo list) are shown on
 * the summary page. Everything the template and the script need is prepared here, so the
 * view only prints values.
 */
class ViewModel
{
    public const ATTRIBUTE_PREFIX = 'data-js-guide-wizard-';

    private StepFactory $stepFactory;
    private ResultFactory $resultFactory;
    private ScriptStrings $scriptStrings;

    public function __construct(
        private EscHtml&TranslateNoopedPlural&WpStripAllTags $wpService,
    ) {
        $this->stepFactory = new StepFactory(new OptionFactory($wpService));
        $this->resultFactory = new ResultFactory($wpService);
        $this->scriptStrings = new ScriptStrings($wpService);
    }

    /**
     * @param array $fields   Module fields (after FieldTransform)
     * @param array $lang     Strings from Lang::getLang()
     * @param int   $moduleId Module post id, used for unique element ids
     */
    public function create(array $fields, array $lang, int $moduleId, string $postTitle = ''): array
    {
        $guideId = 'mod-guide-wizard-' . $moduleId;
        $steps = [];

        foreach ($fields['steps'] ?? [] as $index => $step) {
            $steps[] = $this->stepFactory->create((array) $step, (int) $index, $guideId);
        }

        $questions = $this->addPresentation(
            array_values(array_filter($steps, static fn(array $step): bool => $step['hasOptions'])),
            $lang,
            $guideId,
        );
        $summaryPage = (array) ($fields['summary_page'] ?? []);

        return [
            'id' => $guideId,
            'plainTitle' => trim($this->wpService->wpStripAllTags($postTitle)),
            'restartModalId' => $guideId . '-restart',
            'questions' => $questions,
            'summarySteps' => array_values(array_filter($steps, static fn(array $step): bool => !$step['hasOptions'])),
            'results' => $this->resultFactory->create($summaryPage, $lang),
            'notice' => trim((string) ($summaryPage['notice'] ?? '')),
            'script' => $this->scriptStrings->create($questions, $lang),
        ];
    }

    /**
     * Texts and attributes that depend on the question's position in the guide.
     */
    private function addPresentation(array $questions, array $lang, string $guideId): array
    {
        $total = count($questions);

        foreach ($questions as $index => &$question) {
            $isLast = $index === $total - 1;
            $question['index'] = $index;
            $question['titleId'] = $guideId . '-q' . $index;
            $question['hintId'] = $question['description'] !== '' ? $question['titleId'] . '-hint' : '';
            $question['position'] = sprintf($lang['wizard_question_x_of_y'], $index + 1, $total);
            $question['eyebrow'] = $question['position'] . ' · ' . $question['shortTitle'];
            $question['isFirst'] = $index === 0;
            $question['nextLabel'] = $isLast ? $lang['wizard_show_summary'] : $lang['wizard_continue'];
            $question['attributes'] = [
                self::ATTRIBUTE_PREFIX . 'question' => (string) $index,
                self::ATTRIBUTE_PREFIX . 'type' => $question['inputType'],
                self::ATTRIBUTE_PREFIX . 'short-title' => $question['shortTitle'],
            ];
            // Only the first question is visible before the script has started.
            if ($index > 0) {
                $question['attributes']['hidden'] = 'hidden';
            }
        }
        unset($question);

        return $questions;
    }
}
