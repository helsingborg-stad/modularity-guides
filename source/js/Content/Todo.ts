declare const ajaxurl: string

declare const guides: {
  email_sent: string
  email_failed: string
  lockMessage: string
}

export default (function () {
  const SELECTOR_TODOS_WRAPPER = '.js-modularity-guide-todos'
  const SELECTOR_FORM = '.js-modularity-guide-todos__form'
  const SELECTOR_MODAL_CLOSE_BUTTON =
    '.js-modularity-guide-todos__modal .c-modal__close'
  const SELECTOR_FORM_NOTICE = '.js-modularity-guide-todos__notice'
  const SELECTOR_TABLE = '.js-modularity-guide-todos__table'

  const SELECTOR_INPUT_EMAIL = 'input[name="email"]'

  const NOTICE_LEVEL_CLASSNAMES = {
    info: 'c-notice--info',
    success: 'c-notice--success',
    danger: 'c-notice--danger',
    warning: 'c-notice--warning',
  }
  /**
   * Utility to check if element is visible or not
   * @param {Element} elem
   * @returns {Boolean}
   */
  const isVisible = (elem: HTMLElement) =>
    elem.offsetWidth > 0 ||
    elem.offsetHeight > 0 ||
    elem.getClientRects().length > 0

  /**
   * Extract checklist html from todo table
   * @param {Element} todoTable
   * @returns {String} html with visible checklist items
   */
  function getCheckList(todoTable: Element): string {
    const checklist = todoTable.cloneNode(true) as Element
    document.body.appendChild(checklist)

    // Remove not visible rows
    checklist?.querySelectorAll('tr')?.forEach(row => {
      if (isVisible(row)) {
        return
      }
      row.remove()
    })

    const checklistHTML = checklist.outerHTML
    checklist.remove()
    return encodeURI(checklistHTML)
  }

  /**
   * Send checklist email through wordpress on submit
   * @param {SubmitEvent} e
   */
  function handleSubmit(e: Event) {
    e.preventDefault()

    const currentForm = e.target as HTMLFormElement
    const currentSection = currentForm?.closest(SELECTOR_TODOS_WRAPPER)
    const email = (currentForm?.querySelector(SELECTOR_INPUT_EMAIL) as HTMLInputElement)?.value

    /**
     * Display or hide form notice
     * @param {String|Boolean} text notice content, set to false to hide notice
     * @param {String} level  notice level - info, danger, warning, success
     * @param {String} icon material icon name
     * @returns
     */
    function setNotice(text: string | boolean, level: keyof typeof NOTICE_LEVEL_CLASSNAMES = 'info', icon: string = '') {
      const noticeElement = currentSection?.querySelector(SELECTOR_FORM_NOTICE)
      const noticeIconElement = noticeElement?.querySelectorAll('c-icon')
      const noticeClassList = noticeElement?.classList
      const noticeLevelClassName = NOTICE_LEVEL_CLASSNAMES[level]

      if (typeof text ==='boolean' && text === false) {
        if (!noticeClassList?.contains('u-display--none')) {
          noticeClassList?.add('u-display--none')
        }

        return
      }

      if (
        noticeClassList && noticeLevelClassName &&
        !noticeClassList?.contains(noticeLevelClassName)
      ) {
        ;[...noticeClassList]
          .filter(className =>
            Object.values(NOTICE_LEVEL_CLASSNAMES).includes(className)
          )
          .forEach(className => {
            noticeClassList?.remove(className)
          })

        noticeClassList?.add(noticeLevelClassName)
      }

      if (typeof text === 'string' && text.length > 0) {
        const spanElements = Array.from(noticeElement?.querySelectorAll('span') ?? [])
        const textSpan =
          spanElements.length === 2 ? spanElements[1] : spanElements[0]
        textSpan.innerHTML = text

        if (icon.length > 0 && noticeIconElement) {
          noticeIconElement.forEach(x => x.innerHTML = icon)
        }

        noticeClassList?.remove('u-display--none')
      }
    }

    setNotice(false)

    const todoTable = currentSection?.querySelector(SELECTOR_TABLE)

    if (todoTable && email && ajaxurl) {
      currentSection?.classList.toggle('is-loading')

      const checklist = getCheckList(todoTable)
      const data = {
        action: 'email_todo',
        checklist: checklist,
        email: email,
      }

      fetch(ajaxurl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: new Headers({
          'Content-Type': 'application/x-www-form-urlencoded',
        }),
        body: new URLSearchParams(data),
      })
        .then(function (response) {
          currentSection?.classList.toggle('is-loading')
          response.json().then(data => {
            if (data) {
              currentSection?.classList.toggle('is-loading')

              setNotice(guides.email_sent, 'success', 'report')
              setTimeout(function () {
                setNotice(false)
                const closeModal = currentSection?.querySelector(
                  SELECTOR_MODAL_CLOSE_BUTTON
                ) as HTMLButtonElement
                closeModal?.click()
              }, 2000)
            } else {
              setNotice(guides.email_failed, 'danger', 'report')
            }
          })
        })
        .catch(function (error) {
          console.log(JSON.stringify(error))
        })
    }
  }

  /**
   *
   * @param {Element} todoSection
   * @param {Number} index
   */
  function subscribeForm(todoSection: Element) {
    todoSection
      ?.querySelector(SELECTOR_FORM)
      ?.addEventListener('submit', handleSubmit)
  }

  /**
   * Query ToDo sections & initialize great things
   */
  function init() {
    const todoSections = document.querySelectorAll(SELECTOR_TODOS_WRAPPER)
    if (todoSections?.length > 0) {
      todoSections.forEach((todoSection) => {
        subscribeForm(todoSection)
      })
    }
  }

  window.addEventListener('DOMContentLoaded', init)
})()
