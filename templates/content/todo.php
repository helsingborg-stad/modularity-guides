<table class="table mod-guide-todo-list">
<thead>
    <tr>
        <th class="text-center" width="20"><?php _e('Done', 'modularity-guides'); ?></th>
        <th><?php _e('Title', 'modularity-guides'); ?></th>
        <th><?php _e('Link', 'modularity-guides'); ?></th>
    </tr>
</thead>
<tbody>
<?php foreach ($content['list_items'] as $item) : ?>
    <tr <?php if (isset($item['toggle_key']) && !empty($item['toggle_key'])) : ?>data-mod-guide-toggle-key-content="<?php echo $item['toggle_key']; ?>"<?php endif; ?>>
        <td class="text-center"><span class="mod-guide-todo-check"></span></td>
        <td><?php echo $item['title']; ?></td>
        <td>
            <?php if (isset($item['link_url']) && !empty($item['link_url'])) : ?>
            <a href="<?php echo $item['link_url']; ?>" class="link-item"><?php echo isset($item['link_text']) && !empty($item['link_text']) ? $item['link_text'] : 'Mer information'; ?></a>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
