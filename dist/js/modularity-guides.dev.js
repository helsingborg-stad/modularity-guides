var ModularityGuides = {};

ModularityGuides = ModularityGuides || {};
ModularityGuides.Content = ModularityGuides.Content || {};

ModularityGuides.Content.Checkboxes = (function ($) {

    function Checkboxes() {
        this.handleEvents();
        this.contentToggleEngine();
    }

    Checkboxes.prototype.handleEvents = function() {
        $('input[type="checkbox"][data-mod-guide-relation]').on('change', function (e) {
            var relations = $(this).data('mod-guide-relation');
            relations = relations.split(',');

            $.each(relations, function (index, item) {
                var $cb = $('input[type="checkbox"][data-mod-guide-toggle-key="' + item + '"]');
                $cb.prop('checked', !$cb.prop('checked')).trigger('change');
            });
        });

        $('[data-mod-guide-toggle-key]').on('change', function (e) {
            this.contentToggleEngine();
        }.bind(this));
    };

    Checkboxes.prototype.contentToggleEngine = function() {
        // Get checked checkboxes
        var checked = [];
        var $checkboxes = $('[data-mod-guide-toggle-key]');

        $checkboxes.each(function (index, element) {
            if ($(element).prop('checked') !== true) {
                return;
            }

            checked.push($(element).attr('data-mod-guide-toggle-key'));
        });

        // Display or hide content
        $('[data-mod-guide-toggle-key-content]').each(function (index, element) {
            var shouldShow = false;
            var conditions = $(element).attr('data-mod-guide-toggle-key-content');
            conditions = conditions.split(',');

            // Datermind if content should be shown or not
            $.each(conditions, function (index, item) {
                var and = item.match(/(^|\+)([^\+\-]+)/g);
                var andPattern = new RegExp('\\b(' + and.join('|').replace('+', '') + ')\\b', 'ig');
                var andMatches = checked.join(',').match(andPattern);
                var andIsMatching = andMatches !== null && andMatches.length === and.length;

                var andnot = item.match(/\-([^\+\-]+)/g);
                var andnotIsMatching = true;
                if (andnot !== null) {
                    var andnotPattern = new RegExp('\\b(' + andnot.join('|').replace('-', '') + ')\\b', 'ig');
                    var andnotMatches = checked.join(',').match(andnotPattern);
                    andnotIsMatching = !(andnotMatches !== null && andnotMatches.length > 0);
                }

                if (andIsMatching && andnotIsMatching) {
                    shouldShow = true;
                }
            });

            // Hide or show
            if (shouldShow === true) {
                $(element).show();
                return;
            }

            $(element).hide();
            return;
        });
    };

    return new Checkboxes();

})(jQuery);

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

                $container.after('<div class="grid"><div class="grid-md-12"><div class="notice success">' + guides.email_sent + '</div></div></div>')
                location.hash = '';
            });

            return false;
        });
    }

    return new Todo();

})(jQuery);
