// eslint-disable-next-line @typescript-eslint/no-explicit-any
declare const jQuery: any

export default (function ($) {
  function Checkboxes() {
    // @ts-expect-error Needs refactoring
    this.handleEvents()
    // @ts-expect-error Needs refactoring
    this.contentToggleEngine()
  }

  Checkboxes.prototype.handleEvents = function () {
    $('input[type="checkbox"][data-mod-guide-relation]').on(
      'change',
      function () {
        // @ts-expect-error Needs refactoring
        let relations = $(this).data('mod-guide-relation')
        relations = relations.split(',')

        $.each(relations, function (_: number, item: string) {
          const $cb = $(
            'input[type="checkbox"][data-mod-guide-toggle-key="' + item + '"]'
          )
          $cb.prop('checked', !$cb.prop('checked')).trigger('change')
        })
      }
    )

    $('[data-mod-guide-toggle-key]').on(
      'change',
      function () {
        // @ts-expect-error Needs refactoring
        this.contentToggleEngine()
      }.bind(this)
    )
  }

  Checkboxes.prototype.contentToggleEngine = function () {
    // Get checked checkboxes
    const checked: string[] = []
    const $checkboxes = $('[data-mod-guide-toggle-key]')

    $checkboxes.each(function (_: number, element: Element) {
      if ($(element).prop('checked') !== true) {
        return
      }
      checked.push($(element).attr('data-mod-guide-toggle-key'))
    })
    // Hide all todo widgets
    $('[data-mod-guide-todo-widget]').each((
      _: number,
      element: Element
    ) => $(element).hide())
    
    // Display or hide content
    $('[data-mod-guide-toggle-key-content]').each(function (
      _: number,
      element: Element
    ) {
      let shouldShow = false
      let conditions = $(element).attr('data-mod-guide-toggle-key-content')
      conditions = conditions.split(',')

      // Datermind if content should be shown or not
      $.each(conditions, function (_: number, item: string) {
        const and = item.match(/(^|\+)([^+-]+)/g)
        const andPattern = new RegExp(
          '\\b(' + and?.join('|').replace('+', '') + ')\\b',
          'ig'
        )
        const andMatches = checked.join(',').match(andPattern)
        const andIsMatching =
          andMatches !== null && andMatches.length === and?.length

        const andnot = item.match(/-([^+-]+)/g)
        let andnotIsMatching = true
        if (andnot !== null) {
          const andnotPattern = new RegExp(
            '\\b(' + andnot.join('|').replace('-', '') + ')\\b',
            'ig'
          )
          const andnotMatches = checked.join(',').match(andnotPattern)
          andnotIsMatching = !(
            andnotMatches !== null && andnotMatches.length > 0
          )
        }

        if (andIsMatching && andnotIsMatching) {
          shouldShow = true
        }
      })

      // Hide or show
      if (shouldShow) {
        $(element).show()
        $(element).closest('tr').show()
        $(element).closest('[data-mod-guide-todo-widget]').show()
        return
      }

      $(element).hide()
      $(element).closest('tr').hide()
      return
    })
  }
  // @ts-expect-error Needs refactoring
  return new Checkboxes()
})(jQuery)
