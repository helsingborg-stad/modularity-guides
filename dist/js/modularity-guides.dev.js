ModularityGuides = ModularityGuides || {};
ModularityGuides.Navigation = (function ($) {

    function Navigation() {
        $('.modularity-mod-guide [data-guide-nav="next"]').on('click', function (e) {
            var $current = $(e.target).parents('.accordion-section');
            var $next = $current.next('.accordion-section');

            $current.find('[name="active-section"]').prop('checked', false);
            $next.find('[name="active-section"]').prop('checked', true);
        }.bind(this));

        $('.modularity-mod-guide [data-guide-nav="prev"]').on('click', function (e) {
            var $current = $(e.target).parents('.accordion-section');
            var $prev = $current.prev('.accordion-section');

            $current.find('[name="active-section"]').prop('checked', false);
            $prev.find('[name="active-section"]').prop('checked', true);
        }.bind(this));
    }

    return new Navigation();

})(jQuery);

var ModularityGuides = {};

ModularityGuides = ModularityGuides || {};
ModularityGuides.Content = ModularityGuides.Content || {};

ModularityGuides.Content.Checkboxes = (function ($) {

    function Checkboxes() {
        $('[data-mod-guide-toggle-key]').on('change', function (e) {
            this.contentToggleEngine();
        }.bind(this));
    }

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
