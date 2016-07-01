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
