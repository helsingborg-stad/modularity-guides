<div class="grid mod-guide-checkboxes">
    <div class="grid-xs-12">
        <div class="form-group">
            @foreach ($content['checkboxes'] as $checkbox)
                <label>
                    <input type="checkbox" {!! isset($checkbox['relate_to']) && !empty($checkbox['relate_to']) ? 'data-mod-guide-relation="' . $checkbox['relate_to'] . '"' : '' !!} data-mod-guide-toggle-key="{{ $checkbox['key'] }}" {!! isset($checkbox['required']) && $checkbox['required'] ? 'checked disabled' : '' !!}>
                    {{ $checkbox['label'] }}
                    @if (isset($checkbox['required']) && $checkbox['required'])
                        <span class="mod-guide-checkboxes-required">(<?php _e('required', 'modularity-guides'); ?>)</span>
                    @endif
                </label>
            @endforeach
        </div>
    </div>
</div>
