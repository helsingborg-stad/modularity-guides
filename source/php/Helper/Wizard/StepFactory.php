<?php

declare(strict_types=1);

namespace ModularityGuides\Helper\Wizard;

/**
 * Prepares one guide step. Every "checkboxes" layout in the step gets its answer options
 * in `wizard_options`; the step counts as a question when it has at least one option.
 */
class StepFactory
{
    public function __construct(
        private OptionFactory $optionFactory,
    ) {}

    public function create(array $step, int $stepIndex, string $guideId): array
    {
        $type = ($step['selection_type'] ?? 'multiple') === 'single' ? 'radio' : 'checkbox';
        $contents = is_array($step['content'] ?? null) ? $step['content'] : [];
        $optionIndex = 0;
        $outcomes = [];

        foreach ($contents as $contentIndex => &$content) {
            // Unique id for partials that render modals or forms (text_modal, todo).
            $content['stepId'] = $guideId . '-' . $stepIndex . '-' . $contentIndex;

            if (($content['acf_fc_layout'] ?? '') !== 'checkboxes') {
                continue;
            }

            $content['wizard_options'] = [];
            foreach ($content['checkboxes'] ?? [] as $checkbox) {
                $option = $this->optionFactory->create(
                    (array) $checkbox,
                    $type,
                    $guideId . '-' . $stepIndex . '-' . $optionIndex,
                    $guideId . '-q' . $stepIndex,
                    'option-' . $stepIndex . '-' . $optionIndex,
                );
                $content['wizard_options'][] = $option;
                $outcomes[] = $option['outcome'];
                $optionIndex++;
            }
        }
        unset($content);

        $title = (string) ($step['title'] ?? '');
        $shortTitle = trim((string) ($step['short_title'] ?? ''));

        return [
            'title' => $title,
            'shortTitle' => $shortTitle !== '' ? $shortTitle : $title,
            'description' => (string) ($step['description'] ?? ''),
            'help' => (string) ($step['help'] ?? ''),
            'icon' => trim((string) ($step['icon'] ?? '')),
            'inputType' => $type,
            'hasOptions' => $outcomes !== [],
            // Outcome tags are only shown when the question can end the guide.
            'showTags' => array_diff($outcomes, ['pass']) !== [],
            'content' => $contents,
        ];
    }
}
