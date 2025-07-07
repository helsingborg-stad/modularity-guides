import './Content/Todo'
import './Content/Checkboxes'

// Required selectors
const SELECTOR_MODULARITY_GUIDE = '.js-modularity-guide'
const SELECTOR_SECTION = '.js-modularity-guide__section'
const SELECTOR_NEXT = '.js-modularity-guide__next'
const SELECTOR_PREV = '.js-modularity-guide__prev'
const SELECTOR_ACCORDION_TOGGLE = '[js-expand-button]'

// Required data-attributes
const ATTRIBUTE_STEP = 'data-guide-step'

/**
 * Click handler for Next & Prev buttons,
 * will traverse up to find current, previous and the next step to simulate accordion click
 * @param {*} e event
 */
function handlePrevNextClick(e) {
  e.preventDefault()

  console.log('next prev')

  const prevNextButtonElement = e.target
  const isNext = e.target.classList.contains(SELECTOR_NEXT.substring(1))

  // Traverse DOM upwards
  const currentGuide = prevNextButtonElement?.closest(SELECTOR_MODULARITY_GUIDE)
  const currentSection = prevNextButtonElement?.closest(SELECTOR_SECTION)
  const currentStep = parseInt(
    currentSection?.getAttribute(ATTRIBUTE_STEP) ?? '-1'
  )

  if (currentStep > 0) {
    const targetStep = isNext ? currentStep + 1 : currentStep - 1
    const targetSection = currentGuide?.querySelector(
      `[${ATTRIBUTE_STEP}="${targetStep}"]`
    )
    const targetToggle = targetSection?.querySelector(SELECTOR_ACCORDION_TOGGLE)

    if (targetToggle) {
      targetToggle.click()
    }
  }
}

/**
 * Subscribe prev/next button click event
 * @param {Element} wrapperElement
 */
function subscribePrevNextButtons(wrapperElement) {
  const buttons = [
    ...wrapperElement.querySelectorAll(`${SELECTOR_SECTION} ${SELECTOR_NEXT}`),
    ...wrapperElement.querySelectorAll(`${SELECTOR_SECTION} ${SELECTOR_PREV}`),
  ]

  if (buttons.length > 0) {
    buttons.forEach(buttonElement => {
      buttonElement.addEventListener('click', handlePrevNextClick)
    })
  }
}

/**
 * Query guide modules & initialize stuff
 */
function init() {
  const guideModules = document.querySelectorAll(SELECTOR_MODULARITY_GUIDE)

  if (guideModules && guideModules.length > 0) {
    guideModules.forEach(subscribePrevNextButtons)
  }
}

window.addEventListener('DOMContentLoaded', init)
