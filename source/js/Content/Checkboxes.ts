export default (function ($) {
  function Checkboxes() {
    this.handleEvents()
    this.contentToggleEngine()
  }

  Checkboxes.prototype.handleEvents = function () {
    $('input[type="checkbox"][data-mod-guide-relation]').on(
      'change',
      function (e) {
        let relations = $(this).data('mod-guide-relation')
        relations = relations.split(',')

        $.each(relations, function (index, item) {
          let $cb = $(
            'input[type="checkbox"][data-mod-guide-toggle-key="' + item + '"]'
          )
          $cb.prop('checked', !$cb.prop('checked')).trigger('change')
        })
      }
    )

    $('[data-mod-guide-toggle-key]').on(
      'change',
      function (e) {
        this.contentToggleEngine()
      }.bind(this)
    )
  }

  Checkboxes.prototype.contentToggleEngine = function () {
    // Get checked checkboxes
    let checked = []
    let $checkboxes = $('[data-mod-guide-toggle-key]')

    $checkboxes.each(function (index, element) {
      if ($(element).prop('checked') !== true) {
        return
      }

      checked.push($(element).attr('data-mod-guide-toggle-key'))
    })

    // Display or hide content
    $('[data-mod-guide-toggle-key-content]').each(function (index, element) {
      let shouldShow = false
      let conditions = $(element).attr('data-mod-guide-toggle-key-content')
      conditions = conditions.split(',')

      // Datermind if content should be shown or not
      $.each(conditions, function (index, item) {
        let and = item.match(/(^|\+)([^\+\-]+)/g)
        let andPattern = new RegExp(
          '\\b(' + and.join('|').replace('+', '') + ')\\b',
          'ig'
        )
        let andMatches = checked.join(',').match(andPattern)
        let andIsMatching =
          andMatches !== null && andMatches.length === and.length

        let andnot = item.match(/\-([^\+\-]+)/g)
        let andnotIsMatching = true
        if (andnot !== null) {
          let andnotPattern = new RegExp(
            '\\b(' + andnot.join('|').replace('-', '') + ')\\b',
            'ig'
          )
          let andnotMatches = checked.join(',').match(andnotPattern)
          andnotIsMatching = !(
            andnotMatches !== null && andnotMatches.length > 0
          )
        }

        if (andIsMatching && andnotIsMatching) {
          shouldShow = true
        }
      })

      // Hide or show
      if (shouldShow === true) {
        $(element).show()
        return
      }

      $(element).hide()
      return
    })
  }

  return new Checkboxes()
})(jQuery)
