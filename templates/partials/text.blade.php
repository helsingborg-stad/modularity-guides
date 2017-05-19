<div class="grid mod-guide-text" {!! isset($item['toggle_key']) && !empty($item['toggle_key']) ? 'data-mod-guide-toggle-key-content="' . $item['toggle_key'] . '"' : '' !!}>
    <div class="grid-xs-12">
        <article>
            {!! $content['text'] !!}
        </article>
    </div>
</div>
