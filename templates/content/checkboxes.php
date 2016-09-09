<div class="grid mod-guide-checkboxes">
    <div class="grid-xs-12">
        <div class="form-group">
            <?php foreach ($content['checkboxes'] as $checkbox) : ?>
                <label>
                    <input type="checkbox" data-mod-guide-toggle-key="<?php echo $checkbox['key']; ?>" <?php if (isset($checkbox['required']) && $checkbox['required']) : ?>checked disabled<?php endif; ?>>
                    <?php echo $checkbox['label']; ?>
                    <?php if (isset($checkbox['required']) && $checkbox['required']) : ?><span class="mod-guide-checkboxes-required">(<?php _e('required', 'modularity-guides'); ?>)</span><?php endif; ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</div>
