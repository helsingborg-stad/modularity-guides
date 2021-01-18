<div class="o-grid mod-guide-text u-mb-2" {!! isset($content['toggle_key']) && !empty($content['toggle_key']) ? 'data-mod-guide-toggle-key-content="' . $content['toggle_key'] . '"' : '' !!}>
    <div class="o-grid-12">
        <article>
            {!! $content['text'] !!}
        </article>
    </div>
</div>
