@php
    $i = 1;
    $j = 1;
@endphp

<div class="mod-guide-wrapper">

@if (count($steps) > 0)
    @foreach ($steps as $step)

            @option([
                'type' => 'radio',
                'value' => $i,
                'classList' => ['guideSteps'],
                'attributeList' => [
                    'name' => 'active-section',
                    'aria-pressed' => "false",
                    'guide-step' => $i.'_guide_card',
                    'disabled' => 'disabled'
            ],
                'checked' => ($i === 1) ? true : false
            ])
            @endoption

            @card([
                'collapsible' => true,
                'heading' => '',
                'subHeading' =>'',
                'content' => "<!-- Container hack -->",
                'id' => $i.'_guide_card',
                'classList' =>  [($i !== 1) ? 'guideDiver' : ''],
                'collapsible' => true,
                    'heading' => $i . " " . $step['title'],
                    'buttonColor' => 'black'

            ])

                @if (isset($step['content']) && !empty($step['content']))
                    @foreach ($step['content'] as $content)
                        @include('partials.' . $content['acf_fc_layout'], array('stepId' => $j))
                       @php $j++; @endphp
                    @endforeach
                @endif

                    <div class="guide-pagination">
                        @if ($i >= 1 && $i !== count($steps))

                            @button([
                                'icon' => "keyboard_arrow_right",
                                'reversePositions' => false,
                                'text' => __('Next', 'modularity-guides'),
                                'style' => 'basic',
                                'size' => 'sm',
                                'classList' => ['prevNext','nextStep']
                            ])
                            @endbutton

                        @endif

                        @if ($i !== 1)

                            @button([
                                'icon' => 'keyboard_arrow_left',
                                'reversePositions' => true,
                                'text' => __('Previous', 'modularity-guides'),
                                'style' => 'basic',
                                'size' => 'sm',
                                'classList' => ['prevNext','prevStep', 'u-float--right']
                            ])
                            @endbutton

                        @endif
                    </div>
            @php $i++; @endphp




        @endcard
    @endforeach
@endif
</div>
