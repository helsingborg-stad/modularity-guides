export default (function ($) {

    function Todo() {
        $('#modal-email-todo form').on('submit', function (e) {
            e.preventDefault();

            let $container = $(e.target).parents('.grid').first();
            if ($container.find('textarea[name="g-recaptcha-response"]').val() === '') {
                return false;
            }

            $(this).find('input[type="submit"]').hide();
            $(this).find('.modal-footer').append('<div class="loading"><div></div><div></div><div></div><div></div></div>');

            let $checklist = $container.first().find('table').clone();
            $checklist.appendTo(document.body);
            $checklist.find('tr').not(':visible').remove();
            let checklistHTML = $checklist[0].outerHTML;
            $checklist.remove();

            let checklist = encodeURI(checklistHTML);

            let gCaptcha = $container.find('textarea[name="g-recaptcha-response"]').val();

            let data = {
                action: 'email_todo',
                checklist: checklist,
                email: $container.find('input[name="email"]').val(),
                captcha: gCaptcha
            };

            $.post(ajaxurl, data, function (response) {
                $container.find('.loading').remove();
                $container.find('input[type="submit"]').show();

                if (response == 'success') {
                    $container.after('<div class="grid"><div class="grid-md-12"><div class="notice success">' + guides.email_sent + '</div></div></div>');
                } else {
                    $container.after('<div class="grid"><div class="grid-md-12"><div class="notice warning">' + guides.email_failed + '</div></div></div>');
                }

                location.hash = '';
            });

            return false;
        });
    }

    return new Todo();

})(jQuery);
