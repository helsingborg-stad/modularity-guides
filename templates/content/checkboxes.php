<div class="grid mod-guide-checkboxes">
    <div class="grid-xs-12">
        <div class="form-group">
            <?php foreach ($content['checkboxes'] as $checkbox) : ?>
                <label>
                    <input type="checkbox" data-mod-guide-toggle-key="<?php echo $checkbox['key']; ?>"> <?php echo $checkbox['label']; ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
</div>
