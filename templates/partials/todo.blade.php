<div class="grid u-mb-2">
    <div class="grid-xs-12">
        <table class="table mod-guide-todo-list">
            <thead>
                <tr>
                    <th class="text-center print-only" width="20"><?php _e('Done', 'modularity-guides'); ?></th>
                    <th><?php _e('Title', 'modularity-guides'); ?></th>
                    <th><?php _e('Link', 'modularity-guides'); ?></th>
                </tr>
            </thead>
            <tbody>
            @foreach ($content['list_items'] as $item)
                <tr {!! isset($item['toggle_key']) && !empty($item['toggle_key']) ? 'data-mod-guide-toggle-key-content="' . $item['toggle_key'] . '"' : '' !!}>
                    <td class="text-center print-only"><span class="mod-guide-todo-check"></span></td>
                    <td>{{ $item['title'] }}</td>
                    <td>
                        @if (isset($item['link_url']) && !empty($item['link_url']))
                        <a href="{{ $item['link_url'] }}" class="link-item">{{ isset($item['link_text']) && !empty($item['link_text']) ? $item['link_text'] : 'Mer information' }}</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot class="hidden-print">
                <tr>
                    <th colspan="3">
                        <a href="#modal-email-todo" class="btn btn-primary btn-sm pricon pricon-email pricon-space-right"><?php _e('Send as email', 'modularity-guides'); ?></button>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div id="modal-email-todo" class="modal modal-backdrop-2 modal-xs" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <a class="btn btn-close" href="#close"></a>
                    <h2 class="modal-title"><?php _e('Send todo-list as email', 'modularity-guides'); ?></h2>
                </div>
                <div class="modal-body">
                    <article>
                        <div class="grid">
                            <div class="grid-md-12">
                                <div class="form-group">
                                    <label for="send-todo-email"><?php _e('Email', 'modularity-guides'); ?></label>
                                    <input type="email" name="email" id="send-todo-email" required>
                                </div>
                            </div>
                        </div>
                        @if(!is_user_logged_in() && $municipio)
                            <div class="grid">
                                <div class="grid-md-12">
                                    <div class="g-recaptcha" data-sitekey="{{ $g_recaptcha_key }}"></div>
                                </div>
                            </div>
                        @endif
                    </article>
                </div>
                <div class="modal-footer">
                    <input type="submit" class="btn btn-primary" value="<?php _e('Send', 'modularity-guides'); ?>">
                </div>
            </form>
        </div>
        <a href="#close" class="backdrop"></a>
    </div>
</div>
