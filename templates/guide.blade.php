<?php
$i = 1;
$j = 1;
?>

<div class="box no-padding">
    <div class="accordion accordion-list">
        @if (count($steps) > 0)
        @foreach ($steps as $step)
        <section class="accordion-section">
            <input type="radio" name="active-section" id="mod-guide-{{ $ID }}-{{ $i }}" {{ $i === 1 ? 'checked' : '' }}>
            <span class="accordion-toggle">
                <h4><span class="label label-number"><em>{{ $i }}</em></span> {{ $step['title'] }}</h4>
            </span>
            <div class="accordion-content">
                @if (isset($step['content']) && count($step['content']) > 0)
                    @foreach ($step['content'] as $content)
                        @include('partials.' . $content['acf_fc_layout'], array('modalId' => $j))
                        <?php $j++; ?>
                    @endforeach
                @endif
                <div class="accordion-nav clearfix">
                    @if ($i > 1)
                        <label class="btn pull-left" data-accordion-nav="prev" for="mod-guide-{{ $ID }}-{{ $i-1 }}"><i class="fa fa-caret-left"></i> <?php _e('Previous', 'modularity-guides'); ?></label>
                    @endif

                    @if (count($steps) > 1 && $i !== count($steps))
                    <label class="btn btn-primary pull-right" data-accordion-nav="next" for="mod-guide-{{ $ID }}-{{ $i+1 }}"><?php _e('Next', 'modularity-guides'); ?> <i class="fa fa-caret-right"></i></label>
                    @endif
                </div>
            </div>
        </section>
        <?php $i++; ?>
        @endforeach
        @endif
    </div>
</div>
