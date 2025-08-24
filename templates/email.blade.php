<html>
    <head>
        <meta charset="UTF-8">
    <style>
        body {
            font-family: Roboto, Arial, sans-serif;
            font-size: 14px;
        }

        div.widget {
            border: 1px solid black;
            border-top-width: 6px;
            border-radius: 6px;
            padding: 16px;
            padding-top: 0;
            margin-bottom: 16px;

            div.action {
                margin-top: 12px;
            }

            table {
                width: 100%;
                margin-top: 12px;
            }

            input {
                padding: 8;
            }

            td:last-child {
                text-align: right;
            }
        }
    </style>
    </head>
    <body>
        <p>{{ $lang['email_intro'] }}</p>
        @foreach ($content as $group => $items)
        <div class="widget">
            <h3>{{ $group }}</h3>
                @foreach ($items as $item)
                    @if (isset($item['link_text']) && !empty($item['link_text']))
                        @php
                            $link = '';
                            if(isset($item['link_url']) && !empty($item['link_url'])) {
                                $link = ' <a href="' . $item['link_url'] . '" target="_blank">' . $lang['read_more'] . '</a>';
                            }
                        @endphp
                            <div class="action">
                                <input type="checkbox" id="{{$item['id']}}" />
                                <label for="{{$item['id']}}">{!! $item['link_text'] . $link !!}</label>
                            </div>
                    @endif
                @endforeach
        </div>
        @endforeach
    </body>
</html>
