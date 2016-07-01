<?php $modalId = isset($modalId) ? $modalId + 1 : 1; ?>
<div class="grid mod-guide-text" <?php if (isset($content['toggle_key']) && !empty($content['toggle_key'])) : ?>data-mod-guide-toggle-key-content="<?php echo $content['toggle_key']; ?>"<?php endif; ?>>
    <div class="grid-xs-12">
        <article>
            <h3><a href="#mod-guide-modal-<?php echo $modalId; ?>"><?php echo $content['title']; ?></a></h3>
            <?php echo $content['summary']; ?>
        </article>

        <div id="mod-guide-modal-<?php echo $modalId; ?>" class="modal modal-backdrop-2 modal-small" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-content">
                <div class="modal-header">
                    <a class="btn btn-close" href="#close"></a>
                    <h2 class="modal-title"><?php echo $content['title']; ?></h2>
                </div>
                <div class="modal-body">
                    <article>
                        <?php echo $content['modal_content']; ?>
                    </article>
                </div>
                <div class="modal-footer">
                    <a href="#close" class="btn btn-default"><?php _e('Close', 'modularity-guides'); ?></a>
                </div>
            </div>
            <a href="#close" class="backdrop"></a>
        </div>
    </div>
</div>
