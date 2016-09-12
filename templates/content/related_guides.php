<div class="grid">
    <div class="grid-xs-12">
        <ul>
            <?php foreach ($content['guides'] as $item) : ?>
            <li <?php if (isset($item['toggle_key']) && !empty($item['toggle_key'])) : ?>data-mod-guide-toggle-key-content="<?php echo $item['toggle_key']; ?>"<?php endif; ?>>
                <a class="link-item" href="<?php echo $item['url']; ?>"><?php echo $item['title']; ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
