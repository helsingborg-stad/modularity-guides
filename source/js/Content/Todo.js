const SELECTOR_TODOS_WRAPPER = '.js-modularity-guide-todos';
const SELECTOR_FORM = '.js-modularity-guide-todos__form';
const SELECTOR_TABLE = '.js-modularity-guide-todos__table';
const SELECTOR_INPUT_RECAPTCHA = 'textarea[name="g-recaptcha-response"]';
const SELECTOR_INPUT_EMAIL = 'input[name="email"]';

export default (function ($) {
    /**
     * Check if element is visible or not
     * @param {*} elem 
     * @returns {boolean}
     */
    const isVisible = (elem) => (elem.offsetWidth > 0 || elem.offsetHeight > 0 || elem.getClientRects().length > 0);

    /**
     * Extract checklist html from todo table
     * @param {*} todoTable 
     * @returns {string} html containing visible checklist items
     */
    function getCheckList(todoTable) {
        let checklist = todoTable.cloneNode(true);
        checklist = document.body.appendChild(checklist);
    
        // Remove not visible rows
        checklist?.querySelectorAll('tr')?.forEach(row => {
            if (isVisible(row)) {
                return;
            }
            row.remove();
        });
        
        let checklistHTML = checklist.outerHTML;
        checklist.remove();
        return encodeURI(checklistHTML);
    }
    
    /**
     * Send checklist email through wordpress on submit
     * @param {*} e 
     */
    function handleSubmit(e) {
        e.preventDefault();
    
        const currentForm = e.target;
        const currentSection = currentForm.closest(SELECTOR_TODOS_WRAPPER);
        const recaptcha = currentForm?.querySelector(SELECTOR_INPUT_RECAPTCHA)?.val();
        const email = currentForm?.querySelector(SELECTOR_INPUT_EMAIL)?.value;
    
        if (recaptcha === '') {
            return false;
        }
    
        const todoTable = currentSection?.querySelector(SELECTOR_TABLE);
    
        if (todoTable && email && ajaxurl) {
            const checklist = getCheckList(todoTable); 
            const data = {
                action: 'email_todo',
                checklist: checklist,
                email: email,
                captcha: recaptcha
            };
    
            $.post(ajaxurl, data, function (response) {
                if (response == 'success') {
                    console.log('success!');
                } else {
                    console.log('fails!');
                }
    
                location.hash = '';
            });
        }
    }
    
    /**
     * Query ToDo sections & subscribe form
     */
    function subscribeSubmit() {
        const todoSections = document.querySelectorAll(SELECTOR_TODOS_WRAPPER);
        if (todoSections?.length > 0) {
            todoSections.forEach(todoSection => {
                const form = todoSection?.querySelector(SELECTOR_FORM);
                if (form) {
                    form.addEventListener('submit', handleSubmit);
                }
            });
        }
    }


    window.addEventListener('DOMContentLoaded', subscribeSubmit);
})(jQuery);
