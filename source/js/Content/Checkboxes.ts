export default (() => {
	const SELECTOR_RELATION_CHECKBOX = 'input[type="checkbox"][data-mod-guide-relation]';
	const SELECTOR_TOGGLE_KEY = '[data-mod-guide-toggle-key]';
	const SELECTOR_TODO_WIDGET = '[data-mod-guide-todo-widget]';
	const SELECTOR_TOGGLE_KEY_CONTENT = '[data-mod-guide-toggle-key-content]';
	const ATTRIBUTE_RELATION = 'data-mod-guide-relation';
	const ATTRIBUTE_TOGGLE_KEY = 'data-mod-guide-toggle-key';
	const ATTRIBUTE_TOGGLE_KEY_CONTENT = 'data-mod-guide-toggle-key-content';

	/**
	 * Toggle checkboxes related to the changed checkbox, based on data-mod-guide-relation
	 * @param {Event} e change event
	 */
	function handleRelationChange(e: Event) {
		const checkbox = e.currentTarget as HTMLInputElement;
		const relations = (checkbox.getAttribute(ATTRIBUTE_RELATION) ?? '')
			.split(',')
			.map((item) => item.trim())
			.filter((item) => item !== '');

		relations.forEach((item) => {
			const relatedCheckbox = document.querySelector<HTMLInputElement>(
				`input[type="checkbox"][${ATTRIBUTE_TOGGLE_KEY}="${item}"]`,
			);

			if (!relatedCheckbox) {
				return;
			}

			relatedCheckbox.checked = !relatedCheckbox.checked;
			relatedCheckbox.dispatchEvent(new Event('change'));
		});
	}

	/**
	 * Show or hide content based on which toggle keys are currently checked
	 */
	function contentToggleEngine() {
		const checked: string[] = [];

		document.querySelectorAll<HTMLInputElement>(SELECTOR_TOGGLE_KEY).forEach((element) => {
			if (element.checked !== true) {
				return;
			}
			checked.push(element.getAttribute(ATTRIBUTE_TOGGLE_KEY) ?? '');
		});

		document.querySelectorAll<HTMLElement>(SELECTOR_TODO_WIDGET).forEach((element) => {
			element.style.display = 'none';
		});

		document.querySelectorAll<HTMLElement>(SELECTOR_TOGGLE_KEY_CONTENT).forEach((element) => {
			let shouldShow = false;
			const conditions = (element.getAttribute(ATTRIBUTE_TOGGLE_KEY_CONTENT) ?? '')
				.split(',')
				.map((item) => item.trim())
				.filter((item) => item !== '');

			conditions.forEach((item) => {
				const and = item.match(/(^|\+)([^+-]+)/g);
				const andPattern = new RegExp(`\\b(${and?.join('|').replace('+', '')})\\b`, 'ig');
				const andMatches = checked.join(',').match(andPattern);
				const andIsMatching = andMatches !== null && andMatches.length === and?.length;

				const andnot = item.match(/-([^+-]+)/g);
				let andnotIsMatching = true;
				if (andnot !== null) {
					const andnotPattern = new RegExp(`\\b(${andnot.join('|').replace('-', '')})\\b`, 'ig');
					const andnotMatches = checked.join(',').match(andnotPattern);
					andnotIsMatching = !(andnotMatches !== null && andnotMatches.length > 0);
				}

				if (andIsMatching && andnotIsMatching) {
					shouldShow = true;
				}
			});

			const row = element.closest<HTMLElement>('tr');

			if (shouldShow) {
				element.style.display = 'revert';
				if (row) {
					row.style.display = 'revert';
				}
				const todoWidget = element.closest<HTMLElement>(SELECTOR_TODO_WIDGET);
				if (todoWidget) {
					todoWidget.style.display = 'revert';
				}
				return;
			}

			element.style.display = 'none';
			if (row) {
				row.style.display = 'none';
			}
		});
	}

	/**
	 * Subscribe checkbox relation & content toggle events
	 */
	function handleEvents() {
		document.querySelectorAll<HTMLInputElement>(SELECTOR_RELATION_CHECKBOX).forEach((checkbox) => {
			checkbox.addEventListener('change', handleRelationChange);
		});

		document.querySelectorAll<HTMLElement>(SELECTOR_TOGGLE_KEY).forEach((element) => {
			element.addEventListener('change', contentToggleEngine);
		});
	}

	/**
	 * Subscribe events & set initial content visibility
	 */
	function init() {
		handleEvents();
		contentToggleEngine();
	}

	window.addEventListener('DOMContentLoaded', init);
})();
