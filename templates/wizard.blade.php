@php
    $j = 1;
    $questions = $wizard['questions'];
    $total = count($questions);
    $guideId = 'mod-guide-wizard-' . $id;
@endphp

<div class="mod-guide-wizard js-modularity-guide js-mod-guide-wizard"
    id="{{ $guideId }}"
    data-js-modularity-guide-post-url="{{ $apiUrl }}"
    data-guide-title="{{ wp_strip_all_tags((string) ($postTitle ?? '')) }}">

    <div class="mod-guide-wizard__layout">
        {{-- Sidebar with progress and steps --}}
        <aside class="mod-guide-wizard__side" aria-label="{{ $lang['wizard_progress_label'] }}">
            @if (!$hideTitle && !empty($postTitle))
                <p class="mod-guide-wizard__side-title">{!! $postTitle !!}</p>
            @endif
            <div class="mod-guide-wizard__progress-label">
                <span data-wizard-progress-position></span>
                <span data-wizard-progress-answered></span>
            </div>
            <progress class="mod-guide-wizard__progress" max="{{ $total }}" value="0" data-wizard-progress aria-hidden="true"></progress>
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
                <section class="mod-guide-wizard__question"
                    data-wizard-question="{{ $index }}"
                    data-wizard-type="{{ $question['inputType'] }}"
                    data-wizard-short-title="{{ $question['shortTitle'] }}"
                    aria-labelledby="{{ $guideId }}-q{{ $index }}"
                    @if ($index > 0) hidden @endif>

                    <p class="mod-guide-wizard__eyebrow">
                        {{ sprintf($lang['wizard_question_x_of_y'], $index + 1, $total) }} · {{ $question['shortTitle'] }}
                    </p>

                    <div class="mod-guide-wizard__head">
                        <div class="mod-guide-wizard__head-text">
                            <h2 class="mod-guide-wizard__title" id="{{ $guideId }}-q{{ $index }}" tabindex="-1">{{ $question['title'] }}</h2>
                            @if (!empty($question['description']))
                                <p class="mod-guide-wizard__hint" id="{{ $guideId }}-q{{ $index }}-hint">{{ $question['description'] }}</p>
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
                                        <label class="mod-guide-wizard__option" for="{{ $guideId }}-{{ $option['id'] }}">
                                            <input
                                                type="{{ $question['inputType'] }}"
                                                id="{{ $guideId }}-{{ $option['id'] }}"
                                                name="{{ $guideId }}-q{{ $index }}"
                                                value="{{ $option['value'] }}"
                                                data-wizard-outcome="{{ $option['outcome'] }}"
                                                data-wizard-reason="{{ $option['reason'] }}"
                                                data-wizard-label="{{ $option['label'] }}"
                                                @if ($option['toggleKey'] !== '') data-mod-guide-toggle-key="{{ $option['toggleKey'] }}" @endif
                                                @if ($option['relation'] !== '') data-mod-guide-relation="{{ $option['relation'] }}" @endif
                                            >
                                            <span class="mod-guide-wizard__option-main">
                                                {{ $option['label'] }}
                                                @if (!empty($option['description']))
                                                    <span class="mod-guide-wizard__option-desc">{{ $option['description'] }}</span>
                                                @endif
                                            </span>
                                            @if ($question['showTags'])
                                                <span class="mod-guide-wizard__tag mod-guide-wizard__tag--{{ $option['outcome'] }}">{{ $lang['wizard_tag_' . $option['outcome']] }}</span>
                                            @endif
                                        </label>
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
                        <details class="mod-guide-wizard__help">
                            <summary>{{ $lang['wizard_help'] }}</summary>
                            <div class="c-article">{!! $question['help'] !!}</div>
                        </details>
                    @endif
                </section>
            @endforeach

            {{-- Summary --}}
            <section class="mod-guide-wizard__result" data-wizard-result hidden aria-labelledby="{{ $guideId }}-result-title">
                @foreach ($wizard['results'] as $type => $result)
                    <div class="mod-guide-wizard__hero mod-guide-wizard__hero--{{ $type }}" data-wizard-hero="{{ $type }}" hidden>
                        <div class="mod-guide-wizard__hero-symbol" aria-hidden="true">
                            @icon(['icon' => $type === 'pass' ? 'check' : ($type === 'stop' ? 'close' : 'question_mark'), 'size' => 'lg'])@endicon
                        </div>
                        <div>
                            @if (!empty($postTitle))
                                <p class="mod-guide-wizard__eyebrow">{!! $postTitle !!}</p>
                            @endif
                            <h2 class="mod-guide-wizard__hero-title" tabindex="-1" data-wizard-hero-title>{{ $result['title'] }}</h2>
                            <p class="mod-guide-wizard__hero-reason" data-wizard-reason hidden></p>
                            <p data-wizard-hero-text>{{ $result['text'] }}</p>
                        </div>
                    </div>
                @endforeach
                <h2 class="u-sr__only" id="{{ $guideId }}-result-title">{{ $lang['wizard_summary'] }}</h2>

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
                        'attributeList' => ['data-wizard-restart' => '']
                    ])
                    @endbutton
                </div>

                <div class="mod-guide-wizard__notice mod-guide-wizard__notice--warning" data-wizard-remaining hidden></div>

                <div class="mod-guide-wizard__result-grid @if (empty($wizard['summarySteps'])) mod-guide-wizard__result-grid--split @endif">
                    <div class="mod-guide-wizard__panel">
                        <h3>{{ $lang['wizard_your_answers'] }}</h3>
                        <dl class="mod-guide-wizard__answers" data-wizard-answers></dl>
                        <p class="mod-guide-wizard__meta" data-wizard-created></p>
                    </div>

                    @if (!empty($wizard['summarySteps']))
                        @foreach ($wizard['summarySteps'] as $step)
                            <div class="mod-guide-wizard__panel">
                                <h3>{{ $step['title'] }}</h3>
                                @if (!empty($step['description']))
                                    <p class="mod-guide-wizard__hint">{{ $step['description'] }}</p>
                                @endif
                                @foreach ($step['content'] as $content)
                                    @include('partials.' . $content['acf_fc_layout'], ['stepId' => $j])
                                    @php $j++; @endphp
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>

                @if (!empty($wizard['notice']))
                    <div class="mod-guide-wizard__notice">
                        @icon(['icon' => 'info', 'size' => 'md'])@endicon
                        <p>{{ $wizard['notice'] }}</p>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <dialog class="mod-guide-wizard__dialog" data-wizard-dialog aria-labelledby="{{ $guideId }}-dialog-title">
        <h2 id="{{ $guideId }}-dialog-title">{{ $lang['wizard_restart_title'] }}</h2>
        <p>{{ $lang['wizard_restart_text'] }}</p>
        <div class="mod-guide-wizard__dialog-actions">
            @button([
                'text' => $lang['wizard_cancel'],
                'style' => 'outlined',
                'type' => 'button',
                'attributeList' => ['data-wizard-dialog-cancel' => '', 'autofocus' => '']
            ])
            @endbutton
            @button([
                'text' => $lang['wizard_restart_confirm'],
                'style' => 'filled',
                'color' => 'primary',
                'type' => 'button',
                'attributeList' => ['data-wizard-dialog-confirm' => '']
            ])
            @endbutton
        </div>
    </dialog>

    <div class="mod-guide-wizard__toast" data-wizard-toast role="status" hidden></div>
</div>
