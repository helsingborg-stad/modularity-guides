ModularityGuides = ModularityGuides || {};
ModularityGuides.Content = ModularityGuides.Content || {};

ModularityGuides.Content.Todo = (function ($) {

    function Todo() {
        $('#modal-email-todo form').on('submit', function (e) {
            e.preventDefault();

            var $container = $(e.target).parents('.grid').first();
            if ($container.find('textarea[name="g-recaptcha-response"]').val() === '') {
                return false;
            }

            $(this).find('input[type="submit"]').hide();
            $(this).find('.modal-footer').append('<div class="loading"><div></div><div></div><div></div><div></div></div>');

            var $checklist = $container.first().find('table').clone();
            $checklist.appendTo(document.body);
            $checklist.find('tr').not(':visible').remove();
            checklistHTML = $checklist[0].outerHTML;
            $checklist.remove();

            checklist = encodeURI(checklistHTML);

            var gCaptcha = $container.find('textarea[name="g-recaptcha-response"]').val();

            var data = {
                action: 'email_todo',
                checklist: checklist,
                email: $container.find('input[name="email"]').val(),
                captcha: gCaptcha
            };

            $.post(ajaxurl, data, function (response) {
                $container.find('.loading').remove();
                $container.find('input[type="submit"]').show();

                if (response != 'success') {
                    return false;
                }

                $container.after('<div class="grid"><div class="grid-md-12"><div class="notice success">Email was sent.</div></div></div>')
                location.hash = '';
            });

            return false;
        });
    }

    return new Todo();

})(jQuery);
