<div class="grid mod-guide-checkboxes u-mb-2">
    <div class="grid-xs-12">
        <div class="form-group">
            @foreach ($content['checkboxes'] as $checkbox)
                <label>

                    @option([
                        'type' => 'checkbox',
                        'attributeList' => [
                            'name' => 'active-section',
                            'data-mod-guide-relation' => (isset($checkbox['relate_to']) && !empty($checkbox['relate_to']) ) ? $checkbox['relate_to'] : '',
                            'data-mod-guide-toggle-key' => $checkbox['key']
                        ],
                        'required' => (isset($checkbox['required']) && $checkbox['required']) ? true : false,
                        'label' => $checkbox['label']
                    ])
                    @endoption

                    @if (isset($checkbox['required']) && $checkbox['required'])
                        <span class="mod-guide-checkboxes-required">(<?php _e('required', 'modularity-guides'); ?>)</span>
                    @endif
                </label>
            @endforeach
        </div>
    </div>
</div>
