<?php

declare(strict_types=1);

namespace ModularityGuides\Helper;

class FieldTransform
{
    protected $fields = [];

    public function __construct(array $fields)
    {
        $this->fields = $fields;

        // Transform todo list items into groups based on title
        $todo = &$this->getTodo();

        if (isset($todo['list_items'])) {
            $grouped = [];
            foreach ($todo['list_items'] as &$item) {
                $key = $item['title'];
                $grouped[$key][] = [
                    'id' => uniqid(),
                    'link_text' => $item['link_text'],
                    'link_url' => $item['link_url'],
                    'toggle_key' => $item['toggle_key'],
                ];
            }
            // Replace list items with grouped version
            $todo['list_items'] = $grouped;
        }
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function &getTodo(): array
    {
        foreach ($this->fields['steps'] as &$step) {
            foreach ($step['content'] as &$content) {
                if ($content['acf_fc_layout'] != 'todo') {
                    continue;
                }

                return $content;
            }
        }
        return [];
    }

    public function filterTodo(array $keys): array
    {
        $todo = $this->getTodo();

        if (isset($todo['list_items'])) {
            foreach ($todo['list_items'] as $group => &$items) {
                foreach ($items as $key => $item) {
                    if (in_array($item['toggle_key'], $keys)) {
                        continue;
                    }

                    unset($items[$key]);
                }
                // No active actions, remove group
                if (empty($items)) {
                    unset($todo['list_items'][$group]);
                }
            }
        }
        return $todo['list_items'];
    }
}
