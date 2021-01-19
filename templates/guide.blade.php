@php
    $i = 1;
    $j = 1;
@endphp

<div class="mod-guide-wrapper js-modularity-guide">
    <style>
        .mod-guide-wrapper button > * {
            pointer-events: none;
        }
    </style>

@if (count($steps) > 0)
    @accordion([])
        @foreach ($steps as $step)
            @accordion__item([
                'heading' => $loop->iteration . '. '  . $step['title'],
                'classList' => ['js-modularity-guide__section'],
                'attributeList' => ['data-guide-step' => $loop->iteration]
            ])

                @if (isset($step['content']) && !empty($step['content']))
                    @foreach ($step['content'] as $content)
                        @include('partials.' . $content['acf_fc_layout'], array('stepId' => $j))
                        @php $j++; @endphp
                    @endforeach
                @endif

                @notice([
                    'type' => 'danger',
                    'message' => [
                        'text' => 'hbgWorks',
                        'size' => 'sm'
                    ],
                    'classList' => ['c-notice-guide'],
                    'icon' => [
                        'name' => 'report',
                        'size' => 'md',
                        'color' => 'black'
                    ]
                ])
                @endnotice

                <div class="guide-pagination">
                    <div class="o-grid">
                        <div class="o-grid-6">
                            @if (!$loop->first)
                                @button([
                                    'icon' => 'keyboard_arrow_left',
                                    'reversePositions' => true,
                                    'text' => __('Previous', 'modularity-guides'),
                                    'style' => 'filled',
                                    // 'size' => 'sm',
                                    'classList' => ['prevNext','prevStep', 'js-modularity-guide__prev']
                                ])
                                @endbutton
                            @endif
                        </div>
                        <div class="o-grid-6 u-text-align--right">
                            @if (!$loop->last)
                                @button([
                                    'icon' => "keyboard_arrow_right",
                                    'reversePositions' => false,
                                    'text' => __('Next', 'modularity-guides'),
                                    'style' => 'filled',
                                    // 'size' => 'sm',
                                    'color' => 'primary',
                                    'classList' => ['prevNext','nextStep', 'js-modularity-guide__next'],
                                ])
                                @endbutton
                            @endif
                        </div>
                    </div>
                </div>
                @php $i++; @endphp
            @endaccordion__item
        @endforeach
        @endaccordion
    @endif
</div>
