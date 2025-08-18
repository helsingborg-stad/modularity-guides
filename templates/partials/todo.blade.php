
<div class="o-grid modularity-guide-todos js-modularity-guide-todos">
    <div class="o-grid-12">

            <?php
                $grouped = [];
                foreach ($content['list_items'] as $item) {
                    $key = $item['title'];
                    $grouped[$key][] = $item;
                }
            ?>
            @foreach ($grouped as $group => $items)
                @paper(['classList' => [
                    'u-margin__bottom--4'
                ]])
                    @typography([
                        "variant" => "h4",
                        "element" => "h4"
                    ])
                        {{ $group }}
                    @endtypography
                    <table>
                            @foreach ($items as $item)
                                @if (isset($item['link_text']) && !empty($item['link_text']))
                    <tr>
                    <td>
                            @option([
                                'type' => 'checkbox',
                                'label' => $item['link_text'], 'attributeList' => [
                    'data-mod-guide-toggle-key-content' => isset($item['toggle_key']) && !empty($item['toggle_key']) ? $item['toggle_key'] : ''
                ]
                            ])
                            @endoption
                            </td>
                            <td>
                            @if (isset($item['link_url']) && !empty($item['link_url']))
                                @link([
                                    'href' => $item['link_url']
                                ])
                                    Mer information
                                @endlink
                            @endif
                            </td>
                            </tr>
                    @endif
                    @endforeach

                            </table>
                @endpaper
            @endforeach        

            @button( [
                'href' => '',
                'icon' => 'mail',
                'color' => 'primary',
                'style' => 'filled',
                'reversePositions' => true,
                'size' => 'sm',
                'text' => $lang['send_as_email'],
                'attributeList' => [
                    'data-open' => "mod-guide-todo-".$stepId,
                ],
                'classList' => ['js-modularity-guide-todos__modal-trigger']
            ])
            @endbutton

        @modal([
            'heading' => $lang['send_todo_list'],
            'isPanel' => false,
            'id' => "mod-guide-todo-".$stepId,
            'overlay' => 'dark',
            'animation' => 'scale-up',
            'size' => 'sm',
            'classList' => ['js-modularity-guide-todos__modal'],
            'attributeList' => [
                'tabindex' => "-1",
                'role' => "dialog",
                'aria-hidden' => "true"
            ]
        ])
            @form([
                'method' => 'POST',
                'classList' => ['js-modularity-guide-todos__form']
            ])
                <div class="o-grid o-grid--no-margin">
                    <div class="o-grid-12 u-margin__bottom--3">
                        @field([
                            'type' => 'email',
                            'id' => 'send-todo-email',
                            'name' => 'email',
                            'autocomplete' => 'email',
                            'invalidMessage' => $lang['email_error'],
                            'label' => $lang['email'],
                            'required' => true,
                        ])
                        @endfield
                    </div>
                    
                    <div class="o-grid-12">
                        <div class="o-grid o-grid--no-margin">
                            <div class="o-grid-fit">
                                @button([
                                    'text' => $lang['send'],
                                    'color' => 'primary',
                                    'style' => 'filled',
                                    'type' => 'submit'
                                ])
                                @endbutton
                            </div>
                            <div class="o-grid-fit">
                                @loader([
                                    'size' => 'sm',
                                    'classList' => []
                                ])
                                @endloader
                            </div>
                        </div>
                    </div>
                </div>
            @endform
            @slot('bottom')
                @notice([
                    'type' => 'success',
                    'message' => [
                        'text' => $lang['notice'],
                        'size' => 'sm'
                    ],
                    'classList' => ['js-modularity-guide-todos__notice', 'u-display--none'],
                    'icon' => [
                        'name' => 'check',
                        'size' => 'md',
                        'color' => 'white'
                    ]
                ])
                @endnotice
            @endslot

        @endmodal
    </div>
</div>

