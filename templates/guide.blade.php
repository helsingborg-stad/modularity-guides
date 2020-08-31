@php
    $i = 1;
    $j = 1;
@endphp

<div class="box no-padding">
    <div class="accordion accordion-list">
        @if (count($steps) > 0)
        @foreach ($steps as $step)
            <section class="accordion-section">

                <input type="radio" name="active-section" id="mod-guide-{{ $ID }}-{{ $i }}" {{ $i === 1 ? 'checked' : '' }}>
                @typography([
                    'element' => "span",
                    'classList' => ['accordion-toggle']
                ])
                    @typography([
                        'element' => "h4",
                    ])
                        @typography([
                            'element' => "span",
                            'classList' => ['label', 'label-number']

                        ])
                            @typography([
                                'element' => "em",
                            ])
                                {{ $i }}
                            @endtypography

                        @endtypography

                        {{ $step['title'] }}

                    @endtypography
                @endtypography

                <div class="accordion-content">
                    @if (isset($step['content']) && count($step['content']) > 0)
                        @foreach ($step['content'] as $content)
                            @include('partials.' . $content['acf_fc_layout'], array('stepId' => $j))
                           @php $j++; @endphp
                        @endforeach
                    @endif
                    <div class="accordion-nav clearfix">
                        @if ($i > 1)

                            @typography([
                                'element' => "label",
                                'classList' => ['btn, 'pull-left'],
                                'attributeList' = [
                                        'data-accordion-nav' => 'prev',
                                        'for' => 'mod-guide-'.$ID.'--'.$i-1
                                ]
                            ])
                                @icon([
                                    'icon' => 'chevron_left',
                                    'size' => 'xs'
                                ])
                                @endicon
                                {{_e('Previous', 'modularity-guides')}}
                            @endtypography

                        @endif

                        @if (count($steps) > 1 && $i !== count($steps))
                                @typography([
                                    'element' => "label",
                                    'classList' => ['btn, 'pull-left'],
                                    'attributeList' = [
                                        'data-accordion-nav' => 'next',
                                        'for' => 'mod-guide-'.$ID.'--'.$i+1
                                    ]
                                ])
                                    {{_e('Next', 'modularity-guides')}}
                                    @icon([
                                        'icon' => 'chevron_right',
                                        'size' => 'xs'
                                    ])
                                    @endicon
                                @endtypography
                        @endif
                    </div>
                </div>
            </section>
            @php $i++; @endphp
        @endforeach
        @endif
    </div>
</div>
