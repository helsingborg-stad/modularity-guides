import './Content/Checkboxes.js';
import './Content/Todo.js';

/**
 * Guide steps and views
 */
class GuideDefault {

    /**
     * Init
     */
    constructor() {
        this.initView();
    }

    /**
     * Init the first step in guide
     * Activate all steps
     */
    initView() {

        let int = 0;
        for (const firstElement of document.body.querySelectorAll('.c-card')) {
            if (int < 1) {
                firstElement.querySelector('.c-card__body').classList.Border = '1px solid red';
                firstElement.querySelector('.c-card__body').setAttribute('c-card--collapse', 'c-card--collapse')
                firstElement.querySelector('.c-card__body').classList.remove('c-card--collapse');
            }
            int++;
        }

        for (const stepOption of document.body.querySelectorAll('.c-option__radio--hidden-box')) {
            stepOption.disabled = false;
        }

        this.prepareEvent();
        this.prevNextStep();
    }

    /**
     * Change Step in guide
     */
    prepareEvent() {

        const self = this;
        for (const stepOption of document.body.querySelectorAll('.c-option__radio--hidden-box')) {
            stepOption.addEventListener("click", function () {
                self.changeView(this);
            }, false);
        }
    }

    /**
     * Change view
     */
    changeView(element) {

        for (const hideOption of document.querySelectorAll('.c-card .c-card__body')) {
            hideOption.removeAttribute('c-card--collapse');
            hideOption.classList.add('c-card--collapse');
        }

        const thisElementId = element.getAttribute('guide-step');
        document.getElementById(thisElementId).querySelector('.c-card__body').setAttribute('c-card--collapse', 'c-card--collapse');
        document.getElementById(thisElementId).querySelector('.c-card__body').classList.remove('c-card--collapse');

    }

    /**
     * Jump to next or previous
     */
    prevNextStep() {

        const self = this;
        for (const steps of document.body.querySelectorAll('.prevNext')) {

            steps.addEventListener("click", function () {

                let currentStep = null;
                let count = 0;
                let stepData = [];

                for (const stepCurrent of document.body.querySelectorAll('.guideSteps .c-option__radio--hidden-box')) {
                    stepData.push(stepCurrent.getAttribute('id'));
                    if (stepCurrent.checked) {
                        currentStep = stepCurrent.getAttribute('value');
                    }
                    count++;
                }

                if (this.classList.contains('nextStep')) {
                    let next = parseInt(currentStep);
                    document.getElementById(stepData[next]).checked = true;
                    self.changeView(document.getElementById(stepData[next]));
                }

                if (this.classList.contains('prevStep')) {
                    let prev = parseInt(currentStep) - 2;
                    document.getElementById(stepData[prev]).checked = true;
                    self.changeView(document.getElementById(stepData[prev]));
                }

            }, false);
        }
    }
}

new GuideDefault();
