import { hook } from './hooks';
import { setText } from './strings';

const VISIBLE_MS = 4500;

/**
 * Short confirmation message, e.g. after saving the answers as text.
 */
export class Toast {
	private timer: number | undefined;

	constructor(private readonly element: HTMLElement | null) {}

	show(text: string): void {
		const element = this.element;
		if (!element) {
			return;
		}
		setText(element.querySelector(hook('toast-text')), text);
		element.hidden = false;
		window.clearTimeout(this.timer);
		this.timer = window.setTimeout(() => {
			element.hidden = true;
		}, VISIBLE_MS);
	}
}
