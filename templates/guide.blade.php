{{-- Guide module (step by step). All data is prepared in Helper\Wizard\ViewModel. --}}
@if (!$wizard['hasQuestions'])
    {{-- No answer options: show the content as it is, without the step by step frame. --}}
    @element(['classList' => ['mod-guide-wizard', 'mod-guide-wizard--content-only', 'js-modularity-guide'], 'attributeList' => ['data-js-modularity-guide-post-url' => $apiUrl]])
        @foreach ($wizard['summarySteps'] as $step)
            @paper(['classList' => ['mod-guide-wizard__panel', 'u-margin__bottom--3']])
                @typography(['element' => 'h3', 'variant' => 'h3', 'classList' => ['u-margin__bottom--2']])
                    {{ $step['title'] }}
                @endtypography
                @foreach ($step['content'] as $content)
                    @include('partials.' . $content['acf_fc_layout'], ['stepId' => $content['stepId']])
                @endforeach
            @endpaper
        @endforeach
    @endelement
@else
@element([
    'classList' => ['mod-guide-wizard', 'js-modularity-guide'],
    'attributeList' => [
        'id' => $wizard['id'],
        'data-js-guide-wizard' => json_encode($wizard['script']),
        'data-js-guide-wizard-title' => $wizard['plainTitle'],
        'data-js-modularity-guide-post-url' => $apiUrl,
    ],
])
    @element(['classList' => ['mod-guide-wizard__layout']])
        {{-- Sidebar with progress and steps --}}
        @element([
            'componentElement' => 'aside',
            'classList' => ['mod-guide-wizard__side'],
            'attributeList' => ['aria-label' => $lang['wizard_progress_label']],
        ])
            @if (!$hideTitle && !empty($postTitle))
                @typography(['element' => 'p', 'variant' => 'h4', 'classList' => ['mod-guide-wizard__side-title', 'u-margin__bottom--2']])
                    {!! $postTitle !!}
                @endtypography
            @endif
            @element(['classList' => ['mod-guide-wizard__progress-label', 'u-display--flex', 'u-justify-content--space-between', 'u-gap--1', 'u-margin__bottom--1']])
                @element(['componentElement' => 'span', 'hideIfNoContent' => false, 'attributeList' => ['data-js-guide-wizard-progress-position' => '']])
                @endelement
                @element(['componentElement' => 'span', 'hideIfNoContent' => false, 'attributeList' => ['data-js-guide-wizard-progress-answered' => '']])
                @endelement
            @endelement
            @progressBar([
                'value' => 0,
                'classList' => ['mod-guide-wizard__progress'],
                'attributeList' => ['data-js-guide-wizard-progress' => '', 'aria-hidden' => 'true'],
            ])
            @endprogressBar
            @element(['componentElement' => 'ol', 'classList' => ['mod-guide-wizard__steps', 'u-unlist']])
                @foreach ($wizard['questions'] as $question)
                    @element(['componentElement' => 'li'])
                        @button([
                            'text' => $question['shortTitle'],
                            'style' => 'basic',
                            'size' => 'sm',
                            'fullWidth' => true,
                            'ripple' => false,
                            'classList' => ['mod-guide-wizard__step'],
                            'attributeList' => ['data-js-guide-wizard-jump' => (string) $question['index'], 'disabled' => 'disabled'],
                        ])
                            @element(['componentElement' => 'span', 'classList' => ['mod-guide-wizard__step-dot'], 'attributeList' => ['aria-hidden' => 'true']])
                                @element(['componentElement' => 'span', 'classList' => ['mod-guide-wizard__step-number']])
                                    {{ $question['index'] + 1 }}
                                @endelement
                                @icon(['icon' => 'check', 'size' => 'sm', 'classList' => ['mod-guide-wizard__step-icon', 'mod-guide-wizard__step-icon--pass']])@endicon
                                @icon(['icon' => 'close', 'size' => 'sm', 'classList' => ['mod-guide-wizard__step-icon', 'mod-guide-wizard__step-icon--stop']])@endicon
                                @icon(['icon' => 'question_mark', 'size' => 'sm', 'classList' => ['mod-guide-wizard__step-icon', 'mod-guide-wizard__step-icon--review']])@endicon
                            @endelement
                        @endbutton
                    @endelement
                @endforeach
                @element(['componentElement' => 'li'])
                    @button([
                        'text' => $lang['wizard_summary'],
                        'style' => 'basic',
                        'size' => 'sm',
                        'fullWidth' => true,
                        'ripple' => false,
                        'classList' => ['mod-guide-wizard__step'],
                        'attributeList' => ['data-js-guide-wizard-jump' => 'summary', 'disabled' => 'disabled'],
                    ])
                        @element(['componentElement' => 'span', 'classList' => ['mod-guide-wizard__step-dot'], 'attributeList' => ['aria-hidden' => 'true']])
                            @icon(['icon' => 'checklist', 'size' => 'sm'])@endicon
                        @endelement
                    @endbutton
                @endelement
            @endelement
        @endelement

        @element(['classList' => ['mod-guide-wizard__main']])
            {{-- Questions --}}
            @foreach ($wizard['questions'] as $question)
                @paper([
                    'classList' => ['mod-guide-wizard__question'],
                    'attributeList' => $question['attributes'],
                ])
                    @typography(['element' => 'p', 'variant' => 'meta', 'classList' => ['mod-guide-wizard__eyebrow', 'u-margin__bottom--2']])
                        {{ $question['eyebrow'] }}
                    @endtypography

                    @element(['classList' => ['mod-guide-wizard__head', 'u-display--flex', 'u-align-items--center', 'u-gap--3', 'u-margin__bottom--3']])
                        @element(['classList' => ['mod-guide-wizard__head-text', 'u-flex-grow--1']])
                            @typography([
                                'element' => 'h2',
                                'variant' => 'h2',
                                'id' => $question['titleId'],
                                'classList' => ['mod-guide-wizard__title'],
                                'attributeList' => ['tabindex' => '-1'],
                            ])
                                {{ $question['title'] }}
                            @endtypography
                            @if ($question['hintId'])
                                @typography(['id' => $question['hintId'], 'classList' => ['mod-guide-wizard__hint', 'u-margin__bottom--0']])
                                    {{ $question['description'] }}
                                @endtypography
                            @endif
                        @endelement
                        @if ($question['icon'])
                            @element(['classList' => ['mod-guide-wizard__art'], 'attributeList' => ['aria-hidden' => 'true']])
                                @icon(['icon' => $question['icon'], 'size' => 'xl'])@endicon
                            @endelement
                        @endif
                    @endelement

                    @element(['classList' => ['mod-guide-wizard__form']])
                        @foreach ($question['content'] as $content)
                            @if ($content['acf_fc_layout'] === 'checkboxes')
                                @element([
                                    'componentElement' => 'fieldset',
                                    'classList' => ['mod-guide-wizard__options'],
                                    'attributeList' => $question['hintId'] ? ['aria-describedby' => $question['hintId']] : [],
                                ])
                                    @element(['componentElement' => 'legend', 'classList' => ['u-sr__only']])
                                        {{ $question['title'] }}
                                    @endelement
                                    @foreach ($content['wizard_options'] as $option)
                                        @element(['classList' => ['mod-guide-wizard__option']])
                                            @option([
                                                'type' => $option['type'],
                                                'id' => $option['id'],
                                                'name' => $option['name'],
                                                'value' => $option['value'],
                                                'label' => $option['labelHtml'],
                                                'attributeList' => $option['attributes'],
                                            ])
                                            @endoption
                                            @if ($question['showTags'])
                                                @element(['componentElement' => 'span', 'classList' => ['mod-guide-wizard__tag', 'mod-guide-wizard__tag--' . $option['outcome']]])
                                                    {{ $lang['wizard_tag_' . $option['outcome']] }}
                                                @endelement
                                            @endif
                                        @endelement
                                    @endforeach
                                @endelement
                            @else
                                @include('partials.' . $content['acf_fc_layout'], ['stepId' => $content['stepId']])
                            @endif
                        @endforeach

                        @element([
                            'componentElement' => 'p',
                            'hideIfNoContent' => false,
                            'classList' => ['mod-guide-wizard__note', 'u-margin__top--2', 'u-margin__bottom--0'],
                            'attributeList' => ['data-js-guide-wizard-note' => '', 'role' => 'status', 'aria-live' => 'polite'],
                        ])
                        @endelement

                        @element(['classList' => ['mod-guide-wizard__actions', 'u-display--flex', 'u-justify-content--space-between', 'u-align-items--center', 'u-gap--2', 'u-margin__top--2']])
                            @button([
                                'text' => $lang['wizard_back'],
                                'icon' => 'arrow_back',
                                'reversePositions' => true,
                                'style' => 'basic',
                                'classList' => $question['isFirst'] ? ['u-visibility--hidden'] : [],
                                'attributeList' => ['data-js-guide-wizard-back' => ''],
                            ])
                            @endbutton
                            @button([
                                'text' => $question['nextLabel'],
                                'icon' => 'arrow_forward',
                                'style' => 'filled',
                                'color' => 'primary',
                                'attributeList' => ['data-js-guide-wizard-next' => '', 'disabled' => 'disabled'],
                            ])
                            @endbutton
                        @endelement
                    @endelement

                    @if ($question['help'])
                        @accordion(['classList' => ['mod-guide-wizard__help', 'u-margin__top--3']])
                            @accordion__item(['heading' => $lang['wizard_help']])
                                @element(['classList' => ['c-article']])
                                    {!! $question['help'] !!}
                                @endelement
                            @endaccordion__item
                        @endaccordion
                    @endif
                @endpaper
            @endforeach

            {{-- Summary --}}
            @element([
                'componentElement' => 'section',
                'classList' => ['mod-guide-wizard__result'],
                'attributeList' => ['data-js-guide-wizard-result' => '', 'hidden' => 'hidden', 'aria-labelledby' => $wizard['id'] . '-result-title'],
            ])
                @typography(['element' => 'h2', 'id' => $wizard['id'] . '-result-title', 'classList' => ['u-sr__only']])
                    {{ $lang['wizard_summary'] }}
                @endtypography

                @foreach ($wizard['results'] as $result)
                    @notice([
                        'type' => $result['noticeType'],
                        'message' => ['title' => $result['title'], 'text' => $result['messageHtml']],
                        'icon' => ['icon' => $result['icon'], 'size' => 'lg'],
                        'classList' => ['mod-guide-wizard__hero'],
                        'attributeList' => ['data-js-guide-wizard-hero' => $result['type'], 'tabindex' => '-1', 'hidden' => 'hidden'],
                    ])
                    @endnotice
                @endforeach

                @element(['classList' => ['mod-guide-wizard__result-actions', 'u-display--flex', 'u-gap--1', 'u-margin__y--3']])
                    @button([
                        'text' => $lang['wizard_print'],
                        'icon' => 'print',
                        'reversePositions' => true,
                        'style' => 'filled',
                        'color' => 'primary',
                        'attributeList' => ['data-js-guide-wizard-print' => ''],
                    ])
                    @endbutton
                    @button([
                        'text' => $lang['wizard_download'],
                        'icon' => 'download',
                        'reversePositions' => true,
                        'style' => 'outlined',
                        'attributeList' => ['data-js-guide-wizard-download' => ''],
                    ])
                    @endbutton
                    @button([
                        'text' => $lang['wizard_restart'],
                        'icon' => 'restart_alt',
                        'reversePositions' => true,
                        'style' => 'basic',
                        'attributeList' => ['data-open' => $wizard['restartModalId']],
                    ])
                    @endbutton
                @endelement

                @notice([
                    'type' => 'warning',
                    'message' => ['text' => '<span data-js-guide-wizard-remaining-text></span>'],
                    'icon' => ['icon' => 'info', 'size' => 'md'],
                    'classList' => ['u-margin__bottom--3'],
                    'attributeList' => ['data-js-guide-wizard-remaining' => '', 'hidden' => 'hidden'],
                ])
                @endnotice

                @element(['classList' => array_filter(['mod-guide-wizard__result-grid', 'u-margin__top--3', $wizard['summarySteps'] ? '' : 'mod-guide-wizard__result-grid--split'])])
                    @paper(['classList' => ['mod-guide-wizard__panel'], 'attributeList' => ['data-js-guide-wizard-panel' => 'answers']])
                        @typography(['element' => 'h3', 'variant' => 'h3', 'classList' => ['u-margin__bottom--2']])
                            {{ $lang['wizard_your_answers'] }}
                        @endtypography
                        @element([
                            'componentElement' => 'dl',
                            'hideIfNoContent' => false,
                            'classList' => ['mod-guide-wizard__answers'],
                            'attributeList' => ['data-js-guide-wizard-answers' => ''],
                        ])
                        @endelement
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
                                @include('partials.' . $content['acf_fc_layout'], ['stepId' => $content['stepId']])
                            @endforeach
                        @endpaper
                    @endforeach
                @endelement

                @if ($wizard['notice'])
                    @notice([
                        'type' => 'info',
                        'message' => ['text' => e($wizard['notice'])],
                        'icon' => ['icon' => 'info', 'size' => 'md'],
                        'classList' => ['u-margin__top--3'],
                        'attributeList' => ['data-js-guide-wizard-disclaimer' => ''],
                    ])
                    @endnotice
                @endif
            @endelement
        @endelement
    @endelement

    @modal([
        'heading' => $lang['wizard_restart_title'],
        'id' => $wizard['restartModalId'],
        'size' => 'sm',
        'overlay' => 'dark',
        'animation' => 'scale-up',
    ])
        @typography([])
            {{ $lang['wizard_restart_text'] }}
        @endtypography
        @element(['classList' => ['u-display--flex', 'u-justify-content--end', 'u-gap--2', 'u-margin__top--3']])
            @button([
                'text' => $lang['wizard_cancel'],
                'style' => 'outlined',
                'attributeList' => ['data-close' => ''],
            ])
            @endbutton
            @button([
                'text' => $lang['wizard_restart_confirm'],
                'style' => 'filled',
                'color' => 'primary',
                'attributeList' => ['data-close' => '', 'data-js-guide-wizard-restart' => ''],
            ])
            @endbutton
        @endelement
    @endmodal

    @element(['attributeList' => ['data-js-guide-wizard-toast' => '', 'hidden' => 'hidden']])
        @toast(['position' => 'bottom-right'])
            @toast__item([
                'type' => 'success',
                'message' => ['text' => '<span data-js-guide-wizard-toast-text></span>'],
                'icon' => ['icon' => 'check', 'size' => 'md'],
            ])
            @endtoast__item
        @endtoast
    @endelement
@endelement
@endif
