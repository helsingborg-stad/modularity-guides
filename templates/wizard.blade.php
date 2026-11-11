{{-- Step by step (wizard) display mode. All data is prepared in Helper\Wizard. --}}
@php $j = 1; @endphp

<div class="mod-guide-wizard js-modularity-guide"
    id="{{ $wizard['id'] }}"
    data-js-guide-wizard="{{ json_encode($wizard['script']) }}"
    data-js-guide-wizard-title="{{ $wizard['plainTitle'] }}"
    data-js-modularity-guide-post-url="{{ $apiUrl }}">

    <div class="mod-guide-wizard__layout">
        {{-- Sidebar with progress and steps --}}
        <aside class="mod-guide-wizard__side" aria-label="{{ $lang['wizard_progress_label'] }}">
            @if (!$hideTitle && !empty($postTitle))
                @typography(['element' => 'p', 'variant' => 'h4', 'classList' => ['mod-guide-wizard__side-title', 'u-margin__bottom--2']])
                    {!! $postTitle !!}
                @endtypography
            @endif
            <div class="mod-guide-wizard__progress-label u-display--flex u-justify-content--space-between u-gap--1 u-margin__bottom--1">
                <span data-js-guide-wizard-progress-position></span>
                <span data-js-guide-wizard-progress-answered></span>
            </div>
            @progressBar([
                'value' => 0,
                'classList' => ['mod-guide-wizard__progress'],
                'attributeList' => ['data-js-guide-wizard-progress' => '', 'aria-hidden' => 'true']
            ])
            @endprogressBar
            <ol class="mod-guide-wizard__steps">
                @foreach ($wizard['questions'] as $question)
                    <li>
                        <button type="button" class="mod-guide-wizard__step" data-js-guide-wizard-jump="{{ $question['index'] }}" disabled>
                            <span class="mod-guide-wizard__step-dot" aria-hidden="true">
                                <span class="mod-guide-wizard__step-number">{{ $question['index'] + 1 }}</span>
                                @icon(['icon' => 'check', 'size' => 'sm', 'classList' => ['mod-guide-wizard__step-icon', 'mod-guide-wizard__step-icon--pass']])@endicon
                                @icon(['icon' => 'close', 'size' => 'sm', 'classList' => ['mod-guide-wizard__step-icon', 'mod-guide-wizard__step-icon--stop']])@endicon
                                @icon(['icon' => 'question_mark', 'size' => 'sm', 'classList' => ['mod-guide-wizard__step-icon', 'mod-guide-wizard__step-icon--review']])@endicon
                            </span>
                            <span>{{ $question['shortTitle'] }}</span>
                        </button>
                    </li>
                @endforeach
                <li>
                    <button type="button" class="mod-guide-wizard__step" data-js-guide-wizard-jump="summary" disabled>
                        <span class="mod-guide-wizard__step-dot" aria-hidden="true">
                            @icon(['icon' => 'checklist', 'size' => 'sm'])@endicon
                        </span>
                        <span>{{ $lang['wizard_summary'] }}</span>
                    </button>
                </li>
            </ol>
        </aside>

        <div class="mod-guide-wizard__main">
            {{-- Questions --}}
            @foreach ($wizard['questions'] as $question)
                @paper([
                    'classList' => ['mod-guide-wizard__question'],
                    'attributeList' => $question['attributes']
                ])
                    @typography(['element' => 'p', 'variant' => 'meta', 'classList' => ['mod-guide-wizard__eyebrow', 'u-margin__bottom--2']])
                        {{ $question['eyebrow'] }}
                    @endtypography

                    <div class="mod-guide-wizard__head u-display--flex u-align-items--center u-gap--3 u-margin__bottom--3">
                        <div class="mod-guide-wizard__head-text u-flex-grow--1">
                            @typography([
                                'element' => 'h2',
                                'variant' => 'h2',
                                'id' => $question['titleId'],
                                'classList' => ['mod-guide-wizard__title'],
                                'attributeList' => ['tabindex' => '-1']
                            ])
                                {{ $question['title'] }}
                            @endtypography
                            @if ($question['hintId'])
                                @typography(['id' => $question['hintId'], 'classList' => ['mod-guide-wizard__hint', 'u-margin__bottom--0']])
                                    {{ $question['description'] }}
                                @endtypography
                            @endif
                        </div>
                        @if ($question['icon'])
                            <div class="mod-guide-wizard__art" aria-hidden="true">
                                @icon(['icon' => $question['icon'], 'size' => 'xl'])@endicon
                            </div>
                        @endif
                    </div>

                    <div class="mod-guide-wizard__form">
                        @foreach ($question['content'] as $content)
                            @if ($content['acf_fc_layout'] === 'checkboxes')
                                <fieldset class="mod-guide-wizard__options" @if ($question['hintId']) aria-describedby="{{ $question['hintId'] }}" @endif>
                                    <legend class="u-sr__only">{{ $question['title'] }}</legend>
                                    @foreach ($content['wizard_options'] as $option)
                                        <div class="mod-guide-wizard__option">
                                            @option([
                                                'type' => $option['type'],
                                                'id' => $option['id'],
                                                'name' => $option['name'],
                                                'value' => $option['value'],
                                                'label' => $option['labelHtml'],
                                                'attributeList' => $option['attributes']
                                            ])
                                            @endoption
                                            @if ($question['showTags'])
                                                <span class="mod-guide-wizard__tag mod-guide-wizard__tag--{{ $option['outcome'] }}">{{ $lang['wizard_tag_' . $option['outcome']] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </fieldset>
                            @else
                                @include('partials.' . $content['acf_fc_layout'], ['stepId' => $j])
                            @endif
                            @php $j++; @endphp
                        @endforeach

                        <p class="mod-guide-wizard__note u-margin__top--2 u-margin__bottom--0" data-js-guide-wizard-note role="status" aria-live="polite"></p>

                        <div class="mod-guide-wizard__actions u-display--flex u-justify-content--space-between u-align-items--center u-gap--2 u-margin__top--2">
                            @button([
                                'text' => $lang['wizard_back'],
                                'icon' => 'arrow_back',
                                'reversePositions' => true,
                                'style' => 'basic',
                                'type' => 'button',
                                'classList' => $question['isFirst'] ? ['u-visibility--hidden'] : [],
                                'attributeList' => ['data-js-guide-wizard-back' => '']
                            ])
                            @endbutton
                            @button([
                                'text' => $question['nextLabel'],
                                'icon' => 'arrow_forward',
                                'style' => 'filled',
                                'color' => 'primary',
                                'type' => 'button',
                                'attributeList' => ['data-js-guide-wizard-next' => '', 'disabled' => 'disabled']
                            ])
                            @endbutton
                        </div>
                    </div>

                    @if ($question['help'])
                        @accordion(['classList' => ['mod-guide-wizard__help', 'u-margin__top--3']])
                            @accordion__item(['heading' => $lang['wizard_help']])
                                <div class="c-article">{!! $question['help'] !!}</div>
                            @endaccordion__item
                        @endaccordion
                    @endif
                @endpaper
            @endforeach

            {{-- Summary --}}
            <section class="mod-guide-wizard__result" data-js-guide-wizard-result hidden aria-labelledby="{{ $wizard['id'] }}-result-title">
                <h2 class="u-sr__only" id="{{ $wizard['id'] }}-result-title">{{ $lang['wizard_summary'] }}</h2>

                @foreach ($wizard['results'] as $result)
                    @notice([
                        'type' => $result['noticeType'],
                        'message' => ['title' => $result['title'], 'text' => $result['messageHtml']],
                        'icon' => ['icon' => $result['icon'], 'size' => 'lg'],
                        'classList' => ['mod-guide-wizard__hero'],
                        'attributeList' => ['data-js-guide-wizard-hero' => $result['type'], 'tabindex' => '-1', 'hidden' => 'hidden']
                    ])
                    @endnotice
                @endforeach

                <div class="mod-guide-wizard__result-actions u-display--flex u-gap--1 u-margin__y--3">
                    @button([
                        'text' => $lang['wizard_print'],
                        'icon' => 'print',
                        'reversePositions' => true,
                        'style' => 'filled',
                        'color' => 'primary',
                        'type' => 'button',
                        'attributeList' => ['data-js-guide-wizard-print' => '']
                    ])
                    @endbutton
                    @button([
                        'text' => $lang['wizard_download'],
                        'icon' => 'download',
                        'reversePositions' => true,
                        'style' => 'outlined',
                        'type' => 'button',
                        'attributeList' => ['data-js-guide-wizard-download' => '']
                    ])
                    @endbutton
                    @button([
                        'text' => $lang['wizard_restart'],
                        'icon' => 'restart_alt',
                        'reversePositions' => true,
                        'style' => 'basic',
                        'type' => 'button',
                        'attributeList' => ['data-open' => $wizard['restartModalId']]
                    ])
                    @endbutton
                </div>

                @notice([
                    'type' => 'warning',
                    'message' => ['text' => '<span data-js-guide-wizard-remaining-text></span>'],
                    'icon' => ['icon' => 'info', 'size' => 'md'],
                    'classList' => ['u-margin__bottom--3'],
                    'attributeList' => ['data-js-guide-wizard-remaining' => '', 'hidden' => 'hidden']
                ])
                @endnotice

                <div class="mod-guide-wizard__result-grid u-margin__top--3 @if (empty($wizard['summarySteps'])) mod-guide-wizard__result-grid--split @endif">
                    @paper(['classList' => ['mod-guide-wizard__panel'], 'attributeList' => ['data-js-guide-wizard-panel' => 'answers']])
                        @typography(['element' => 'h3', 'variant' => 'h3', 'classList' => ['u-margin__bottom--2']])
                            {{ $lang['wizard_your_answers'] }}
                        @endtypography
                        <dl class="mod-guide-wizard__answers" data-js-guide-wizard-answers></dl>
                        @typography(['variant' => 'meta', 'classList' => ['u-margin__top--2', 'u-margin__bottom--0'], 'attributeList' => ['data-js-guide-wizard-created' => '']])
                        @endtypography
                    @endpaper

                    @foreach ($wizard['summarySteps'] as $step)
                        @paper(['classList' => ['mod-guide-wizard__panel'], 'attributeList' => ['data-js-guide-wizard-panel' => 'content']])
                            @typography(['element' => 'h3', 'variant' => 'h3', 'classList' => ['u-margin__bottom--2']])
                                {{ $step['title'] }}
                            @endtypography
                            @if ($step['description'])
                                @typography(['classList' => ['mod-guide-wizard__hint']])
                                    {{ $step['description'] }}
                                @endtypography
                            @endif
                            @foreach ($step['content'] as $content)
                                @include('partials.' . $content['acf_fc_layout'], ['stepId' => $j])
                                @php $j++; @endphp
                            @endforeach
                        @endpaper
                    @endforeach
                </div>

                @if ($wizard['notice'])
                    @notice([
                        'type' => 'info',
                        'message' => ['text' => e($wizard['notice'])],
                        'icon' => ['icon' => 'info', 'size' => 'md'],
                        'classList' => ['u-margin__top--3'],
                        'attributeList' => ['data-js-guide-wizard-disclaimer' => '']
                    ])
                    @endnotice
                @endif
            </section>
        </div>
    </div>

    @modal([
        'heading' => $lang['wizard_restart_title'],
        'id' => $wizard['restartModalId'],
        'size' => 'sm',
        'overlay' => 'dark',
        'animation' => 'scale-up'
    ])
        @typography([])
            {{ $lang['wizard_restart_text'] }}
        @endtypography
        <div class="u-display--flex u-justify-content--end u-gap--2 u-margin__top--3">
            @button([
                'text' => $lang['wizard_cancel'],
                'style' => 'outlined',
                'type' => 'button',
                'attributeList' => ['data-close' => '']
            ])
            @endbutton
            @button([
                'text' => $lang['wizard_restart_confirm'],
                'style' => 'filled',
                'color' => 'primary',
                'type' => 'button',
                'attributeList' => ['data-close' => '', 'data-js-guide-wizard-restart' => '']
            ])
            @endbutton
        </div>
    @endmodal

    <div data-js-guide-wizard-toast hidden>
        @toast(['position' => 'bottom-right'])
            @toast__item([
                'type' => 'success',
                'message' => ['text' => '<span data-js-guide-wizard-toast-text></span>'],
                'icon' => ['icon' => 'check', 'size' => 'md']
            ])
            @endtoast__item
        @endtoast
    </div>
</div>
