<div class="grid mod-guide-text" {!! isset($content['toggle_key']) && !empty($content['toggle_key']) ? 'data-mod-guide-toggle-key-content="' . $content['toggle_key'] . '"' : '' !!}>
    <div class="grid-xs-12">
        <article>
            {!! $content['text'] !!}
        </article>
    </div>
</div>
