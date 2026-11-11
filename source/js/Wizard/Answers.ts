import { hook } from './hooks';
import { type Outcome, worstOutcome } from './outcome';

/**
 * Reads the visitor's answers straight from the inputs, so the DOM is the only state.
 */
export class Answers {
	constructor(private readonly questions: HTMLElement[]) {}

	get count(): number {
		return this.questions.length;
	}

	question(index: number): HTMLElement | undefined {
		return this.questions[index];
	}

	inputs(index: number): HTMLInputElement[] {
		return [...(this.questions[index]?.querySelectorAll<HTMLInputElement>(hook('outcome')) ?? [])];
	}

	checked(index: number): HTMLInputElement[] {
		return this.inputs(index).filter((input) => input.checked);
	}

	isAnswered(index: number): boolean {
		return this.checked(index).length > 0;
	}

	answeredCount(): number {
		return this.questions.filter((_, index) => this.isAnswered(index)).length;
	}

	outcomeOf(index: number): { outcome: Outcome; reason: string } {
		return worstOutcome(this.checked(index));
	}

	labels(index: number): string[] {
		return this.checked(index).map((input) => input.dataset.jsGuideWizardLabel ?? '');
	}

	shortTitle(index: number): string {
		return this.questions[index]?.dataset.jsGuideWizardShortTitle ?? '';
	}

	isSingleChoice(index: number): boolean {
		return this.questions[index]?.dataset.jsGuideWizardType === 'radio';
	}

	/** Unchecks every answer. Change events keep toggle-key content in sync. */
	clear(): void {
		this.questions.forEach((_, index) => {
			for (const input of this.checked(index)) {
				input.checked = false;
				input.dispatchEvent(new Event('change', { bubbles: true }));
			}
		});
	}
}
