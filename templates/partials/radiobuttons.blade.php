<div class="grid mod-guide-checkboxes u-mb-2">
    <div class="grid-xs-12">
        <fieldset class="form-group">
            @foreach ($content['radiobuttons'] as $radiobutton)
                <label class="mod-guide-radiobuttons--container">
                    @option([
                        'type' => 'radio',
                        'attributeList' => [
                            'name' => 'active-section',
                            'data-mod-guide-relation' => (isset($radiobutton['relate_to']) && !empty($radiobutton['relate_to']) ) ? $radiobutton['relate_to'] : '',
                            'data-mod-guide-toggle-key' => $radiobutton['key'],
                            'data-mod-guide-json' => json_encode($radiobutton['action'])
                        ],
                        'classList' => [
                            'mod-guide-radiobuttons--radiobutton'
                        ],
                        'label' => $radiobutton['label']
                    ])
                    @endoption

                    <span class="mod-guide-radiobuttons--action mod-guide-radiobutton-action-{{ $radiobutton['action']['value'] }}">{{ $radiobutton['action']['label'] }}</span>
                </label>
            @endforeach
        </fieldset>
    </div>
</div>
