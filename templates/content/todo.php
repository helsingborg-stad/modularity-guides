<div class="grid">
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
            <?php foreach ($content['list_items'] as $item) : ?>
                <tr <?php if (isset($item['toggle_key']) && !empty($item['toggle_key'])) : ?>data-mod-guide-toggle-key-content="<?php echo $item['toggle_key']; ?>"<?php endif; ?>>
                    <td class="text-center print-only"><span class="mod-guide-todo-check"></span></td>
                    <td><?php echo $item['title']; ?></td>
                    <td>
                        <?php if (isset($item['link_url']) && !empty($item['link_url'])) : ?>
                        <a href="<?php echo $item['link_url']; ?>" class="link-item"><?php echo isset($item['link_text']) && !empty($item['link_text']) ? $item['link_text'] : 'Mer information'; ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot class="hidden-print">
                <tr>
                    <th colspan="3">
                        <a href="#modal-email-todo" class="btn btn-primary btn-sm pricon
                        pricon-email pricon-space-right"><?php _e('Send as email', 'modularity-guides'); ?></a>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div id="modal-email-todo" class="modal modal-backdrop-2 modal-xs" tabindex="-1" role="dialog" aria-hidden="true">
        <script src='https://www.google.com/recaptcha/api.js'></script>

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
                        <div class="grid">
                            <div class="grid-md-12">
                                <div class="g-recaptcha" data-sitekey="6Lc7xSkTAAAAAJLXT2LbsFDPPp81rKmqzNj0jcH0"></div>
                            </div>
                        </div>
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
