<?php
$i = 1;
$steps = get_field('steps', $module->ID);
var_dump($steps);
?>

<div class="box no-padding">
    <div class="accordion accordion-icon accordion-list">
        <?php if (count($steps) > 0) : foreach ($steps as $step) : ?>
        <section class="accordion-section">
            <input type="radio" name="active-section" id="mod-guide-<?php echo $module->ID; ?>-<?php echo $i; ?>">
            <label class="accordion-toggle" for="mod-guide-<?php echo $module->ID; ?>-<?php echo $i; ?>">
                <h4><span class="label label-number"><em><?php echo $i; ?></em></span> <?php echo $step['title']; ?></h4>
            </label>
            <div class="accordion-content">
                <article>
                    <?php echo $step['text']; ?>
                </article>
            </div>
        </section>
        <?php $i++; endforeach; endif; ?>
    </div>
</div>
