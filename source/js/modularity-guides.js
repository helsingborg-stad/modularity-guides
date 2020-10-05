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
     * Get Section ID
     * @returns {string}
     */
    sectionId() {
        return document.querySelector('.mod-guide-wrapper .c-option__radio ' +
            'input:checked').getAttribute('guide-section');
    }

    /**
     * Init the first step in guide
     * Activate all steps
     */
    initView() {

        let int = 0;
        //Reset stuff - Hack Component library cards to look like "accordish" ......
        for (const firstElement of document.body.querySelectorAll('.c-card')) {
            if (int < 1) {
                firstElement.querySelector('.c-card__body').classList.Border = '1px solid red';
                firstElement.querySelector('.c-card__body').setAttribute(
                    'c-card--collapse', 'c-card--collapse')
                firstElement.querySelector('.c-card__body').classList.remove('c-card--collapse');
            }
            int++;
        }

        for (const disabled of document.body.querySelectorAll('.mod-guide-wrapper ' +
            '.c-option__checkbox--hidden-box')) {
            document.body.querySelector('.mod-guide-wrapper ' +
                '.c-option__radio--hidden-box').removeAttribute('disabled');
        }

        this.prepareEvent();
        this.prevNextStep();
    }

    /**
     * Change Step in guide
     * Prepare for change of view
     */
    prepareEvent() {

        const self = this;

        // Prepare views - Listen to section change
        for (const stepOption of document.body.querySelectorAll('.c-option__radio--hidden-box')) {

            // If item is disabled listen to parent
            if (stepOption.hasAttribute('disabled')) {
                stepOption.parentElement.addEventListener("click", function () {
                    self.requiredNotice(this);
                }, false);
            }

            //Check fore requirements
            stepOption.addEventListener("change", function () {
                self.collectRequiredElements();
                self.changeView(this);
            }, false);
        }
    }

    /**
     * Change view
     * Show next section/step if required input is checked
     */
    changeView(element) {

        // Restore views
        for (const hideOption of document.querySelectorAll('.c-card .c-card__body')) {
            hideOption.removeAttribute('c-card--collapse');
            hideOption.classList.add('c-card--collapse');
        }

        // Change view by adding or removing attributes and css classes -> styleguide
        const thisElementId = element.getAttribute('guide-section');
        document.getElementById(thisElementId).querySelector('.c-card__body').setAttribute(
            'c-card--collapse', 'c-card--collapse');
        document.getElementById(thisElementId).querySelector('.c-card__body').classList.remove(
            'c-card--collapse');

        // Check for required stuff
        if (this.collectRequiredElements()) {
            element.removeAttribute('disabled');
            let int = 0;
            for (const disabled of document.body.querySelectorAll('.mod-guide-wrapper ' +
                '.c-option__checkbox--hidden-box')) {
                if (int === 1) {
                    disabled.setAttribute('disabled', false);
                }
            }
        }
    }

    /**
     * Find required elements
     * @returns {boolean}
     */
    collectRequiredElements() {

        const self = this;
        let requiredFields = [];
        const sectionId = this.sectionId();

        // Run trough section and look for checkboxes that is required
        for (const item of document.querySelectorAll('.' + sectionId + ' .c-option__checkbox--hidden-box')) {

            // Listen to changes of checkbox
            item.addEventListener("change", function () {
                requiredFields = self.evaluateCheckBox({element: this, required: requiredFields});
            }, false);

            // Check if there is anymore input left
            if (item.hasAttribute('required') && !item.checked) {
                requiredFields.push(item.getAttribute('id'));
            } else {
                requiredFields = self.evaluateCheckBox({element: item, required: requiredFields});
            }

            // Lock view if required fields not completed
            if (requiredFields.length <= 0) {
                this.lockView(false);
            } else {
                this.lockView(true);
            }
        }

        // return boolean result
        if (requiredFields.length <= 0) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Evaluate checkboxes
     * @param params
     * @returns {*}
     */
    evaluateCheckBox(params) {

        // Checkbox id
        if (params.element.hasAttribute('required') && !params.element.checked) {
            params.required.push(params.element.getAttribute('id'));
        }

        // Checked or not
        if (params.element.hasAttribute('required') && params.element.checked) {
            for (let int = 0; int < params.required.length; int++) {
                if (params.required.includes(params.element.id)) {
                    params.required.splice(params.required.indexOf(params.element.id), 1);
                }
            }
        }

        // Lock view if not checked
        if (params.required.length <= 0 || params.element.checked) {
            this.lockView(false);
        } else {
            this.lockView(true);
        }

        return params.required;
    }

    /**
     * Jump to next or previous
     * !! Doesnt add up - Needs a second view - Dev ok.
     */
    prevNextStep() {

        const self = this;
        for (const steps of document.body.querySelectorAll('.prevNext')) {

            //Listen to Next or previous clicks
            steps.addEventListener("click", function () {

                let currentStep = 0;
                let count = 0;
                let stepData = [];

                const requirement = self.collectRequiredElements();
                if (!requirement) {
                    return false;
                }

                for (const stepCurrent of document.body.querySelectorAll('.guideSteps ' +
                    '.c-option__radio--hidden-box')) {

                    if (!stepCurrent.checked) {
                        stepData.push(stepCurrent.getAttribute('id'));
                    }

                    if (stepCurrent.checked) {
                        currentStep = stepCurrent.getAttribute('value');
                    }
                    count++;
                }

                // Next section
                if (this.classList.contains('nextStep')) {
                    let next = parseInt(currentStep - 1);
                    document.getElementById(stepData[next]).checked = true;
                    self.changeView(document.getElementById(stepData[next]));
                }

                // Previous section
                if (this.classList.contains('prevStep')) {
                    let prev = parseInt(currentStep) - 2;
                    document.getElementById(stepData[prev]).checked = true;
                    self.changeView(document.getElementById(stepData[prev]));
                }

            }, false);
        }
    }


    /**
     * Lock section view until required are checked
     * @param param (boolean)
     */
    lockView(param) {

        const sectionId = this.sectionId();
        for (const item of document.body.querySelectorAll('.c-option__radio--hidden-box')) {

            //Lock view || not
            if (param) {
                item.setAttribute('disabled', 'disabled');
                document.getElementById(sectionId).querySelector('.prevNext').setAttribute(
                    'disabled', 'disabled');
            } else {
                item.removeAttribute('disabled');
                document.getElementById(sectionId).querySelector('.prevNext').removeAttribute(
                    'disabled');
            }
        }
    }

    /**
     * Notice for unsolved required fields
     */
    requiredNotice(element) {

        if (element.getAttribute('type') === 'checkbox') {
            return false;
        }

        document.querySelector('.c-notice-guide').classList.remove('c-notice-fade-away');
        document.querySelector('.c-notice-guide .c-notice__message--sm').innerHTML = guides.lockMessage;
        document.querySelector('.c-notice-guide').classList.add('displayNotice');
        setTimeout(function () {
            document.querySelector('.c-notice-guide').classList.add('c-notice-fade-away');

        }, 4000);
    }
}

new GuideDefault();
