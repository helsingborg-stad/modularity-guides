@php
    $j = 1;
    $questions = $wizard['questions'];
    $total = count($questions);
    $guideId = 'mod-guide-wizard-' . $id;
    $restartModalId = $guideId . '-restart';
    $noticeTypes = ['pass' => 'success', 'stop' => 'danger', 'review' => 'warning'];
    $noticeIcons = ['pass' => 'check_circle', 'stop' => 'cancel', 'review' => 'help'];
@endphp

<div class="mod-guide-wizard js-modularity-guide js-mod-guide-wizard"
    id="{{ $guideId }}"
    data-js-modularity-guide-post-url="{{ $apiUrl }}"
    data-guide-title="{{ wp_strip_all_tags((string) ($postTitle ?? '')) }}">

    <div class="mod-guide-wizard__layout">
        {{-- Sidebar with progress and steps --}}
        <aside class="mod-guide-wizard__side" aria-label="{{ $lang['wizard_progress_label'] }}">
            @if (!$hideTitle && !empty($postTitle))
                @typography(['element' => 'p', 'variant' => 'h4', 'classList' => ['mod-guide-wizard__side-title']])
                    {!! $postTitle !!}
                @endtypography
            @endif
            <div class="mod-guide-wizard__progress-label">
                <span data-wizard-progress-position></span>
                <span data-wizard-progress-answered></span>
            </div>
            @progressBar([
                'value' => 0,
                'classList' => ['mod-guide-wizard__progress'],
                'attributeList' => ['data-wizard-progress' => '', 'aria-hidden' => 'true']
            ])
            @endprogressBar
            <ol class="mod-guide-wizard__steps">
                @foreach ($questions as $index => $question)
                    <li>
                        <button type="button" class="mod-guide-wizard__step" data-wizard-jump="{{ $index }}" disabled>
                            <span class="mod-guide-wizard__step-dot" data-wizard-step-dot>{{ $index + 1 }}</span>
                            <span>{{ $question['shortTitle'] }}</span>
                        </button>
                    </li>
                @endforeach
                <li>
                    <button type="button" class="mod-guide-wizard__step" data-wizard-jump="summary" disabled>
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
            @foreach ($questions as $index => $question)
                @php
                    $questionAttributes = [
                        'data-wizard-question' => $index,
                        'data-wizard-type' => $question['inputType'],
                        'data-wizard-short-title' => $question['shortTitle'],
                        'role' => 'group',
                        'aria-labelledby' => $guideId . '-q' . $index,
                    ];
                    if ($index > 0) {
                        $questionAttributes['hidden'] = 'hidden';
                    }
                @endphp
                @paper([
                    'classList' => ['mod-guide-wizard__question'],
                    'attributeList' => $questionAttributes
                ])
                    @typography(['element' => 'p', 'variant' => 'meta', 'classList' => ['mod-guide-wizard__eyebrow']])
                        {{ sprintf($lang['wizard_question_x_of_y'], $index + 1, $total) }} · {{ $question['shortTitle'] }}
                    @endtypography

                    <div class="mod-guide-wizard__head">
                        <div class="mod-guide-wizard__head-text">
                            @typography([
                                'element' => 'h2',
                                'variant' => 'h2',
                                'id' => $guideId . '-q' . $index,
                                'classList' => ['mod-guide-wizard__title'],
                                'attributeList' => ['tabindex' => '-1']
                            ])
                                {{ $question['title'] }}
                            @endtypography
                            @if (!empty($question['description']))
                                @typography([
                                    'id' => $guideId . '-q' . $index . '-hint',
                                    'classList' => ['mod-guide-wizard__hint']
                                ])
                                    {{ $question['description'] }}
                                @endtypography
                            @endif
                        </div>
                        @if (!empty($question['icon']))
                            <div class="mod-guide-wizard__art" aria-hidden="true">
                                @icon(['icon' => $question['icon'], 'size' => 'xl'])@endicon
                            </div>
                        @endif
                    </div>

                    <div class="mod-guide-wizard__form" data-wizard-form>
                        @foreach ($question['content'] as $content)
                            @if ($content['acf_fc_layout'] === 'checkboxes')
                                <fieldset class="mod-guide-wizard__options"
                                    @if (!empty($question['description'])) aria-describedby="{{ $guideId }}-q{{ $index }}-hint" @endif>
                                    <legend class="u-sr__only">{{ $question['title'] }}</legend>
                                    @foreach ($content['wizard_options'] as $option)
                                        @php
                                            $optionAttributes = [
                                                'data-wizard-outcome' => $option['outcome'],
                                                'data-wizard-reason' => $option['reason'],
                                                'data-wizard-label' => $option['label'],
                                            ];
                                            if ($option['toggleKey'] !== '') {
                                                $optionAttributes['data-mod-guide-toggle-key'] = $option['toggleKey'];
                                            }
                                            if ($option['relation'] !== '') {
                                                $optionAttributes['data-mod-guide-relation'] = $option['relation'];
                                            }
                                            $optionLabel = e($option['label']);
                                            if (!empty($option['description'])) {
                                                $optionLabel .= '<span class="mod-guide-wizard__option-desc">' . e($option['description']) . '</span>';
                                            }
                                        @endphp
                                        <div class="mod-guide-wizard__option">
                                            @option([
                                                'type' => $question['inputType'],
                                                'id' => $guideId . '-' . $option['id'],
                                                'name' => $guideId . '-q' . $index,
                                                'value' => $option['value'],
                                                'label' => $optionLabel,
                                                'attributeList' => $optionAttributes
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

                        <p class="mod-guide-wizard__note" data-wizard-note role="status" aria-live="polite"></p>

                        <div class="mod-guide-wizard__actions">
                            @button([
                                'text' => $lang['wizard_back'],
                                'icon' => 'arrow_back',
                                'reversePositions' => true,
                                'style' => 'basic',
                                'type' => 'button',
                                'classList' => ['mod-guide-wizard__back', $index === 0 ? 'u-visibility--hidden' : ''],
                                'attributeList' => ['data-wizard-back' => '']
                            ])
                            @endbutton
                            @button([
                                'text' => $index === $total - 1 ? $lang['wizard_show_summary'] : $lang['wizard_continue'],
                                'icon' => 'arrow_forward',
                                'style' => 'filled',
                                'color' => 'primary',
                                'type' => 'button',
                                'attributeList' => ['data-wizard-next' => '', 'disabled' => 'disabled']
                            ])
                            @endbutton
                        </div>
                    </div>

                    @if (!empty($question['help']))
                        @accordion(['classList' => ['mod-guide-wizard__help']])
                            @accordion__item(['heading' => $lang['wizard_help']])
                                <div class="c-article">{!! $question['help'] !!}</div>
                            @endaccordion__item
                        @endaccordion
                    @endif
                @endpaper
            @endforeach

            {{-- Summary --}}
            <section class="mod-guide-wizard__result" data-wizard-result hidden aria-labelledby="{{ $guideId }}-result-title">
                <h2 class="u-sr__only" id="{{ $guideId }}-result-title">{{ $lang['wizard_summary'] }}</h2>

                @foreach ($wizard['results'] as $type => $result)
                    @notice([
                        'type' => $noticeTypes[$type],
                        'message' => [
                            'title' => $result['title'],
                            'text' => '<strong class="mod-guide-wizard__hero-reason" data-wizard-reason hidden></strong>'
                                . '<span data-wizard-hero-text>' . e($result['text']) . '</span>',
                        ],
                        'icon' => ['icon' => $noticeIcons[$type], 'size' => 'lg'],
                        'classList' => ['mod-guide-wizard__hero'],
                        'attributeList' => [
                            'data-wizard-hero' => $type,
                            'data-wizard-hero-title' => '',
                            'tabindex' => '-1',
                            'hidden' => 'hidden',
                        ]
                    ])
                    @endnotice
                @endforeach

                <div class="mod-guide-wizard__result-actions">
                    @button([
                        'text' => $lang['wizard_print'],
                        'icon' => 'print',
                        'reversePositions' => true,
                        'style' => 'filled',
                        'color' => 'primary',
                        'type' => 'button',
                        'attributeList' => ['data-wizard-print' => '']
                    ])
                    @endbutton
                    @button([
                        'text' => $lang['wizard_download'],
                        'icon' => 'download',
                        'reversePositions' => true,
                        'style' => 'outlined',
                        'type' => 'button',
                        'attributeList' => ['data-wizard-download' => '']
                    ])
                    @endbutton
                    @button([
                        'text' => $lang['wizard_restart'],
                        'icon' => 'restart_alt',
                        'reversePositions' => true,
                        'style' => 'basic',
                        'type' => 'button',
                        'attributeList' => ['data-open' => $restartModalId]
                    ])
                    @endbutton
                </div>

                @notice([
                    'type' => 'warning',
                    'message' => ['text' => '<span data-wizard-remaining-text></span>'],
                    'icon' => ['icon' => 'info', 'size' => 'md'],
                    'classList' => ['mod-guide-wizard__remaining'],
                    'attributeList' => ['data-wizard-remaining' => '', 'hidden' => 'hidden']
                ])
                @endnotice

                <div class="mod-guide-wizard__result-grid @if (empty($wizard['summarySteps'])) mod-guide-wizard__result-grid--split @endif">
                    @paper(['classList' => ['mod-guide-wizard__panel']])
                        @typography(['element' => 'h3', 'variant' => 'h3'])
                            {{ $lang['wizard_your_answers'] }}
                        @endtypography
                        <dl class="mod-guide-wizard__answers" data-wizard-answers></dl>
                        @typography(['variant' => 'meta', 'classList' => ['mod-guide-wizard__meta'], 'attributeList' => ['data-wizard-created' => '']])
                        @endtypography
                    @endpaper

                    @foreach ($wizard['summarySteps'] as $step)
                        @paper(['classList' => ['mod-guide-wizard__panel']])
                            @typography(['element' => 'h3', 'variant' => 'h3'])
                                {{ $step['title'] }}
                            @endtypography
                            @if (!empty($step['description']))
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

                @if (!empty($wizard['notice']))
                    @notice([
                        'type' => 'info',
                        'message' => ['text' => e($wizard['notice'])],
                        'icon' => ['icon' => 'info', 'size' => 'md'],
                        'classList' => ['mod-guide-wizard__disclaimer']
                    ])
                    @endnotice
                @endif
            </section>
        </div>
    </div>

    @modal([
        'heading' => $lang['wizard_restart_title'],
        'id' => $restartModalId,
        'size' => 'sm',
        'overlay' => 'dark',
        'animation' => 'scale-up',
        'attributeList' => ['data-wizard-dialog' => '']
    ])
        @typography([])
            {{ $lang['wizard_restart_text'] }}
        @endtypography
        <div class="mod-guide-wizard__dialog-actions">
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
                'attributeList' => ['data-close' => '', 'data-wizard-dialog-confirm' => '']
            ])
            @endbutton
        </div>
    @endmodal

    <div class="mod-guide-wizard__toast" data-wizard-toast hidden>
        @toast(['position' => 'bottom-right'])
            @toast__item([
                'type' => 'success',
                'message' => ['text' => '<span data-wizard-toast-text></span>'],
                'icon' => ['icon' => 'check', 'size' => 'md']
            ])
            @endtoast__item
        @endtoast
    </div>
</div>
