<?php
$i = 1;
$steps = get_field('steps', $module->ID);
?>

<div class="box no-padding">
    <div class="accordion accordion-icon accordion-list">
        <?php if (count($steps) > 0) : foreach ($steps as $step) : ?>
        <section class="accordion-section">
            <input type="radio" name="active-section" id="mod-guide-<?php echo $module->ID; ?>-<?php echo $i; ?>" <?php if ($i === 1) : ?>checked<?php endif; ?>>
            <label class="accordion-toggle" for="mod-guide-<?php echo $module->ID; ?>-<?php echo $i; ?>">
                <h4><span class="label label-number"><em><?php echo $i; ?></em></span> <?php echo $step['title']; ?></h4>
            </label>
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
            </div>
        </section>
        <?php $i++; endforeach; endif; ?>
    </div>
</div>
