import './Content/Todo.js';
import './Content/checkboxes.js';
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
     */
    initView() {

        if (!this.sectionId()) {
            return false;
        }

        let int = 0;

        //Reset stuff and show Section/step 1
        for (const sections of document.querySelectorAll('.mod-guide-wrapper .c-card .c-card__body')) {
            if (int > 0) {
                this.setVisibleSection({element: sections, visible: true});
            } else {
                this.setVisibleSection({element: sections, visible: false});
            }
            int++;
        }

        // Enable the Disabled
        for (const disabled of document.body.querySelectorAll('.mod-guide-wrapper ' +
            '.c-option__checkbox--hidden-box')) {
            document.body.querySelector('.mod-guide-wrapper ' +
                '.c-option__radio--hidden-box').removeAttribute('disabled');
        }

        this.prepareEvent();
        this.prevNextStep();
    }

    /**
     * Set section/Step visibility
     * @param param
     */
    setVisibleSection(param){

        if (param.visible) {
            param.element.classList.add('c-card--collapse');
            param.element.removeAttribute('c-card--collapse');
        } else {
            param.element.classList.remove('c-card--collapse');
            param.element.setAttribute('c-card--collapse', 'c-card--collapse');
        }
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
                    if (!self.collectRequiredElements()) {
                        self.requiredNotice(this);
                    }
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

        // Restore views for sections/steps
        for (const sections of document.querySelectorAll('.mod-guide-wrapper .c-card .c-card__body')) {
            this.setVisibleSection({
                element: sections,
                visible: true
            });
        }

        // Change view by adding or removing attributes and css classes
        this.setVisibleSection({
            element: document.getElementById(element.getAttribute('guide-section')).
                querySelector('.mod-guide-wrapper .c-card__body'),
            visible: false});

        // Check for required stuff
        if (!this.collectRequiredElements()) {
            return false;
        }

        // Able the disabled section
        element.removeAttribute('disabled');

        let int = 0;
        for (const disabled of document.body.querySelectorAll('.mod-guide-wrapper ' +
            '.c-option__checkbox--hidden-box')) {
            if (int === 1) {
                disabled.setAttribute('disabled', false);
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
     * if requirements are ok
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
                    self.requiredNotice(this);
                    return false;
                }

                // Check all step and set current step
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

                // Try to change section by clicking Next section
                if (this.classList.contains('nextStep')) {
                    let next = parseInt(currentStep) - 1;
                    document.getElementById(stepData[next]).checked = true;
                    self.changeView(document.getElementById(stepData[next]));
                }

                // Try to change section by clicking Previous section
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
        for (const item of document.body.querySelectorAll('.mod-guide-wrapper .c-option__radio--hidden-box')) {

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
