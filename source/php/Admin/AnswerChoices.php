<?php

declare(strict_types=1);

namespace ModularityGuides\Admin;

use WpService\Contracts\GetPostMeta;

/**
 * The answers of a saved guide, read from post meta, as choices for "Show when".
 */
class AnswerChoices
{
    public function __construct(
        private GetPostMeta $wpService,
    ) {}

    /**
     * @return array<string, string> [toggle key => "Question: answer"]
     */
    public function forGuide(int $postId): array
    {
        $choices = [];
        $stepCount = (int) $this->meta($postId, 'steps');

        for ($step = 0; $step < $stepCount; $step++) {
            foreach ((array) $this->meta($postId, "steps_{$step}_content") as $index => $layout) {
                if ($layout !== 'checkboxes') {
                    continue;
                }
                $choices += $this->layoutChoices($postId, "steps_{$step}_content_{$index}_checkboxes", $this->question($postId, $step));
            }
        }

        return $choices;
    }

    private function question(int $postId, int $step): string
    {
        $shortTitle = trim((string) $this->meta($postId, "steps_{$step}_short_title"));

        return $shortTitle !== '' ? $shortTitle : (string) $this->meta($postId, "steps_{$step}_title");
    }

    /**
     * @return array<string, string>
     */
    private function layoutChoices(int $postId, string $prefix, string $question): array
    {
        $choices = [];
        $rows = (int) $this->meta($postId, $prefix);

        for ($row = 0; $row < $rows; $row++) {
            $key = trim((string) $this->meta($postId, "{$prefix}_{$row}_key"));
            if ($key !== '') {
                $choices[$key] = $question . ': ' . (string) $this->meta($postId, "{$prefix}_{$row}_label");
            }
        }

        return $choices;
    }

    private function meta(int $postId, string $key): mixed
    {
        return $this->wpService->getPostMeta($postId, $key, true);
    }
}
