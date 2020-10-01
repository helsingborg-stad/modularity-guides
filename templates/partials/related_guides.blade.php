<div class="grid u-mb-2">
    <div class="grid-xs-12">
        <ul>

            @foreach ($content['guides'] as $item)
                <li {!! isset($item['toggle_key']) && !empty($item['toggle_key']) ? 'data-mod-guide-toggle-key-content="' . $item['toggle_key'] . '"' : '' !!}>

                    <a class="link-item" href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                </li>

            @endforeach
        </ul>
    </div>
</div>
