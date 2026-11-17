import type { Answers } from './Answers';
import { hookAttribute } from './hooks';

/** Wrapper around an option, hidden together with it. */
const OPTION_WRAPPER = '.mod-guide-wizard__option';

/**
 * "Show when" on answer options: an option with data-js-guide-wizard-show-when="a,b" is only
 * shown when the visitor has chosen answer a or b in an earlier question.
 *
 * A hidden option is also unchecked and disabled, so an answer that no longer applies (the
 * visitor went back and changed an earlier answer) never counts.
 */
export class Conditions {
	constructor(private readonly answers: Answers) {}

	/** Updates every question in order, so a change cascades to later questions. */
	apply(): void {
		const chosen = new Set<string>();

		for (let index = 0; index < this.answers.count; index++) {
			for (const input of this.answers.inputs(index)) {
				this.update(input, chosen);
			}
			for (const input of this.answers.checked(index)) {
				chosen.add(input.value);
			}
		}
	}

	private update(input: HTMLInputElement, chosen: Set<string>): void {
		const keys = (input.getAttribute(hookAttribute('show-when')) ?? '')
			.split(',')
			.map((key) => key.trim())
			.filter((key) => key !== '');
		if (keys.length === 0) {
			return;
		}

		const visible = keys.some((key) => chosen.has(key));
		const wrapper = input.closest<HTMLElement>(OPTION_WRAPPER);
		if (wrapper) {
			wrapper.hidden = !visible;
		}
		input.disabled = !visible;

		if (!visible && input.checked) {
			input.checked = false;
			// Keeps toggle key content (tips, todo items) in sync.
			input.dispatchEvent(new Event('change', { bubbles: true }));
		}
	}
}
