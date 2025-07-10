@php
    $j = 1;
@endphp

@card()
    <!-- Heading -->
    @if (!$hideTitle && !empty($postTitle))
        <div class="c-card__header">
            @typography([
                'element' => 'h4',
                'variant' => 'p',
                'id'      => 'mod-guide-' . $id .'-label'
            ])
                {!! $postTitle !!}
            @endtypography
        </div>
    @endif

    <!-- Guide specific -->
    <div class="mod-guide-wrapper js-modularity-guide">
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

                        <div class="guide-pagination u-margin__bottom--1">
                            <div class="o-grid o-grid--no-margin u-justify-content--space-between">
                                <div class="o-grid-6">
                                    @if (!$loop->first)
                                        @button([
                                            'icon' => 'keyboard_arrow_left',
                                            'reversePositions' => true,
                                            'text' => $lang['previous'],
                                            'style' => 'filled',
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
                                            'text' => $lang['next'],
                                            'style' => 'filled',
                                            'color' => 'primary',
                                            'classList' => ['prevNext','nextStep', 'js-modularity-guide__next'],
                                        ])
                                        @endbutton
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endaccordion__item
                @endforeach
            @endaccordion
        @endif
    </div>
@endcard