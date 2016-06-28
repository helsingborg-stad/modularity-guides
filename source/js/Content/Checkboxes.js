ModularityGuides = ModularityGuides || {};
ModularityGuides.Content = ModularityGuides.Content || {};

ModularityGuides.Content.Checkboxes = (function ($) {

    function Checkboxes() {
        $('[data-mod-guide-toggle-key]').on('change', function (e) {
            var key = $(this).attr('data-mod-guide-toggle-key');

            if ($(this).prop('checked') === true) {
                $('[data-mod-guide-toggle-key-content="' + key + '"]').show();
                return;
            }

            $('[data-mod-guide-toggle-key-content="' + key + '"]').hide();
            return;
        });
    }

    return new Checkboxes();

})(jQuery);
