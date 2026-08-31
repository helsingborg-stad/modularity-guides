describe('Checkboxes', () => {
	function initializeModule() {
		jest.isolateModules(() => {
			require('./Checkboxes');
		});

		window.dispatchEvent(new Event('DOMContentLoaded'));
	}

	beforeEach(() => {
		document.body.innerHTML = '';
		jest.resetModules();
	});

	it('does not recursively toggle self-related checkboxes and updates matching content', () => {
		document.body.innerHTML = `
			<input type="checkbox" data-mod-guide-relation="self" data-mod-guide-toggle-key="self" />
			<div data-mod-guide-toggle-key-content="self">Description</div>
		`;

		initializeModule();

		const checkbox = document.querySelector('input[type="checkbox"]') as HTMLInputElement;
		const content = document.querySelector('[data-mod-guide-toggle-key-content="self"]') as HTMLElement;

		expect(content.style.display).toBe('none');

		checkbox.checked = true;

		expect(() => checkbox.dispatchEvent(new Event('change'))).not.toThrow();
		expect(checkbox.checked).toBe(true);
		expect(content.style.display).toBe('');
	});
});
