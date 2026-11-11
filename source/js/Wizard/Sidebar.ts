import type { Answers } from './Answers';
import { hook } from './hooks';
import { type Outcome, OUTCOMES } from './outcome';
import { setText, type WizardStrings } from './strings';

/**
 * Progress text, progress bar and the list of steps.
 */
export class Sidebar {
	private readonly stepButtons: HTMLButtonElement[];
	private readonly summaryButton: HTMLButtonElement | null;

	constructor(
		private readonly root: HTMLElement,
		private readonly answers: Answers,
		private readonly strings: WizardStrings,
	) {
		const buttons = [...root.querySelectorAll<HTMLButtonElement>(hook('jump'))];
		this.stepButtons = buttons.filter((button) => button.dataset.jsGuideWizardJump !== 'summary');
		this.summaryButton = buttons.find((button) => button.dataset.jsGuideWizardJump === 'summary') ?? null;
	}

	onJump(goTo: (index: number) => void, showSummary: () => void): void {
		this.stepButtons.forEach((button) => {
			button.addEventListener('click', () => goTo(Number(button.dataset.jsGuideWizardJump)));
		});
		this.summaryButton?.addEventListener('click', showSummary);
	}

	/**
	 * @param current  Index of the visible question, or -1 when the summary is shown
	 * @param furthest Furthest question the visitor has reached
	 * @param hasResult Whether the summary can be shown
	 */
	render(current: number, furthest: number, hasResult: boolean): void {
		const answered = this.answers.answeredCount();

		setText(
			this.root.querySelector(hook('progress-position')),
			current >= 0 ? this.strings.position[current] : this.strings.summary,
		);
		setText(this.root.querySelector(hook('progress-answered')), this.strings.answered[answered]);

		const progress = this.root.querySelector<HTMLElement>(`${hook('progress')} > *`);
		if (progress) {
			progress.style.width = `${(answered / this.answers.count) * 100}%`;
		}

		this.stepButtons.forEach((button, index) => {
			this.renderStep(button, index, current, furthest);
		});

		if (this.summaryButton) {
			this.summaryButton.disabled = !hasResult;
			setCurrent(this.summaryButton, current < 0);
		}
	}

	private renderStep(button: HTMLButtonElement, index: number, current: number, furthest: number): void {
		const isAnswered = this.answers.isAnswered(index);
		let outcome: Outcome | null = null;
		if (isAnswered) {
			// With outcomes hidden, an answered step only shows that it is answered.
			outcome = this.strings.hideOutcomes ? 'pass' : this.answers.outcomeOf(index).outcome;
		}

		button.disabled = index > furthest && !isAnswered;
		button.classList.toggle('is-completed', isAnswered);
		for (const state of OUTCOMES) {
			button.classList.toggle(`is-${state}`, outcome === state);
		}
		setCurrent(button, index === current);
	}
}

function setCurrent(element: HTMLElement, isCurrent: boolean): void {
	if (isCurrent) {
		element.setAttribute('aria-current', 'step');
		return;
	}
	element.removeAttribute('aria-current');
}
