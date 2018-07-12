<div class="grid mod-guide-text u-mb-2" {!! isset($item['toggle_key']) && !empty($item['toggle_key']) ? 'data-mod-guide-toggle-key-content="' . $item['toggle_key'] . '"' : '' !!}>
    <div class="grid-xs-12">
        <article>
            <h3><a href="#mod-guide-modal-{{ $stepId }}">{{ $content['title'] }}</a></h3>
            {!! $content['summary'] !!}
        </article>

        <div id="mod-guide-modal-{{ $stepId }}" class="modal modal-backdrop-2 modal-small" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-content">
                <div class="modal-header">
                    <a class="btn btn-close" href="#close"></a>
                    <h2 class="modal-title">{{ $content['title'] }}</h2>
                </div>
                <div class="modal-body">
                    <article>
                        {!! $content['modal_content'] !!}
                    </article>
                </div>
                <div class="modal-footer">
                    <a href="#close" class="btn btn-default"><?php _e('Close', 'modularity-guides'); ?></a>
                </div>
            </div>
            <a href="#close" class="backdrop"></a>
        </div>
    </div>
</div>
