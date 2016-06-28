<div class="grid mod-guide-checkbox-content">
    <div class="grid-xs-12">
        <article>
            <?php foreach ($content['content'] as $cbContent) : ?>
                <div data-mod-guide-toggle-key-content="<?php echo $cbContent['key']; ?>">
                    <?php echo $cbContent['text']; ?>
                </div>
            <?php endforeach; ?>
        </article>
    </div>
</div>
