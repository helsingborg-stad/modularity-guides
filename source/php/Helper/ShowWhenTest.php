<?php

declare(strict_types=1);

namespace ModularityGuides\Helper;

use PHPUnit\Framework\TestCase;

class ShowWhenTest extends TestCase
{
    public function testShowWhenBecomesToggleKey(): void
    {
        $fields = ShowWhen::apply(['steps' => [[
            'content' => [
                ['acf_fc_layout' => 'text', 'toggle_key' => '', 'show_when' => ['a', 'b']],
                ['acf_fc_layout' => 'todo', 'list_items' => [
                    ['title' => 'Group', 'toggle_key' => '', 'show_when' => ['c']],
                    ['title' => 'Group', 'toggle_key' => '', 'show_when' => []],
                ]],
                ['acf_fc_layout' => 'related_guides', 'guides' => [['toggle_key' => null, 'show_when' => ['d']]]],
            ],
        ]]]);

        $content = $fields['steps'][0]['content'];
        static::assertSame('a,b', $content[0]['toggle_key']);
        static::assertSame('c', $content[1]['list_items'][0]['toggle_key']);
        static::assertSame('', $content[1]['list_items'][1]['toggle_key'], 'Empty "show when" means always shown');
        static::assertSame('d', $content[2]['guides'][0]['toggle_key']);
    }

    public function testToggleKeyTypedByHandWins(): void
    {
        $fields = ShowWhen::apply(['steps' => [[
            'content' => [['acf_fc_layout' => 'text', 'toggle_key' => 'a+b', 'show_when' => ['c']]],
        ]]]);

        static::assertSame('a+b', $fields['steps'][0]['content'][0]['toggle_key']);
    }

    public function testFieldsWithoutStepsAreUnchanged(): void
    {
        static::assertSame([], ShowWhen::apply([]));
    }
}
