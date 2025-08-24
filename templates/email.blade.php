<html>
    <head>
        <meta charset="UTF-8">
        <style>
            body {
                font-family: Roboto, Arial, sans-serif;
                font-size: 14px;
            } 
            div {
                border: 1px solid black;
                border-top-width: 6px;
                border-radius: 6px;
                padding: 16px;
                margin-bottom: 16px;
                table {
                    width: 100%;
                    margin-top: 12px;
                }
                td {
                    vertical-align: top;
                    padding: 0 0 8px 0;
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
        <div>
            <h4>{{ $group }}</h4>
            <table>
                <tbody>
                @foreach ($items as $item)
                    @if (isset($item['link_text']) && !empty($item['link_text']))
                        <tr>
                            <td>
                                <input type="checkbox" id="{{$item['id']}}" />
                                <label for="{{$item['id']}}">{{ $item['link_text'] }}</label>
                            </td>
                            <td>
                                @if (isset($item['link_url']) && !empty($item['link_url']))
                                    <a href="{{ $item['link_url'] }}" target="_blank">{{ $lang['read_more'] }}</a>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </body>
</html>
