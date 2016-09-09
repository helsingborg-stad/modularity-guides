<?php
$i = 1;
$steps = get_field('steps', $module->ID);
?>

<div class="box no-padding">
    <div class="accordion accordion-list">
        <?php if (count($steps) > 0) : foreach ($steps as $step) : ?>
        <section class="accordion-section">
            <input type="radio" name="active-section" id="mod-guide-<?php echo $module->ID; ?>-<?php echo $i; ?>" <?php if ($i === 1) : ?>checked<?php endif; ?>>
            <span class="accordion-toggle">
                <h4><span class="label label-number"><em><?php echo $i; ?></em></span> <?php echo $step['title']; ?></h4>
            </span>
            <div class="accordion-content">
                <?php
                // Include content template
                if (isset($step['content']) && count($step['content']) > 0) {
                    foreach ($step['content'] as $content) {
                        $contentTemplate = MODULARITYGUIDES_TEMPLATE_PATH . '/content/' . $content['acf_fc_layout'] . '.php';
                        if (!file_exists($contentTemplate)) {
                            continue;
                        }

                        include $contentTemplate;
                    }
                }
                ?>
                <div class="accordion-nav clearfix">
                    <?php if ($i > 1) : ?>
                        <label class="btn pull-left" data-accordion-nav="prev" for="mod-guide-<?php echo $module->ID; ?>-<?php echo $i-1; ?>"><i class="fa fa-caret-left"></i> <?php _e('Previous', 'modularity-guides'); ?></label>
                    <?php endif; ?>

                    <?php if (count($steps) > 1 && $i !== count($steps)) : ?>
                    <label class="btn btn-primary pull-right" data-accordion-nav="next" for="mod-guide-<?php echo $module->ID; ?>-<?php echo $i+1; ?>"><?php _e('Next', 'modularity-guides'); ?> <i class="fa fa-caret-right"></i></label>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php $i++; endforeach; endif; ?>
    </div>
</div>
