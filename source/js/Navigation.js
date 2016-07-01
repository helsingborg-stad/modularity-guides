ModularityGuides = ModularityGuides || {};
ModularityGuides.Navigation = (function ($) {

    function Navigation() {
        $('.modularity-mod-guide').each(function (index, element) {
            this.init(element);
        }.bind(this));
    }

    Navigation.prototype.init = function(element) {

    };

    return new Navigation();

})(jQuery);
