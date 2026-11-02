<div class="grid mod-guide-checkboxes u-mb-2">
    <div class="grid-xs-12">
        <div class="form-group">
            @foreach ($content['radiobuttons'] as $radiobutton)
                <label>

                    @option([
                        'type' => 'radio',
                        'attributeList' => [
                            'name' => 'active-section',
                            'data-mod-guide-relation' => (isset($radiobutton['relate_to']) && !empty($radiobutton['relate_to']) ) ? $radiobutton['relate_to'] : '',
                            'data-mod-guide-toggle-key' => $radiobutton['key']
                        ],
                        'classList' => [
                            'mod-guide-checkboxes--checkbox'
                        ],
                        'required' => (isset($radiobutton['required']) && $radiobutton['required']) ? true : false,
                        'label' => $radiobutton['label']
                    ])
                    @endoption

                    @if (isset($radiobutton['required']) && $radiobutton['required'])
                        <span class="mod-guide-checkboxes-required">(<?php echo $lang['required']; ?>)</span>
                    @endif
                </label>
            @endforeach
        </div>
    </div>
</div>
