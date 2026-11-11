import { GuideWizard } from './GuideWizard';
import { toOutcome, worstOutcome } from './outcome';
import type { WizardStrings } from './strings';

const strings: WizardStrings = {
	answered: ['0 answered', '1 answered', '2 answered'],
	remaining: ['0 left', '1 left', '2 left'],
	position: ['Question 1 of 2', 'Question 2 of 2'],
	stoppedAt: ['Stopped at Size.', 'Stopped at Height.'],
	summary: 'Summary',
	chooseOne: 'Choose one',
	chooseMany: 'Choose many',
	savedAnswer: 'Saved answer',
	notePass: 'Note pass',
	noteStop: 'Note stop',
	noteReview: 'Note review',
	yourAnswers: 'Your answers',
	edit: 'Edit',
	notAnswered: 'Not answered',
	outcomeStop: 'Stops',
	outcomeReview: 'Check',
	created: 'Created',
	downloadDone: 'Downloaded',
};

const option = (question: number, value: string, outcome: string, reason = '') =>
	`<input type="radio" name="q${question}" value="${value}" data-js-guide-wizard-outcome="${outcome}"
		data-js-guide-wizard-reason="${reason}" data-js-guide-wizard-label="${value}">`;

const question = (index: number, title: string, options: string) => `
	<div data-js-guide-wizard-question="${index}" data-js-guide-wizard-type="radio" data-js-guide-wizard-short-title="${title}" ${index > 0 ? 'hidden' : ''}>
		<h2 tabindex="-1">${title}</h2>
		${options}
		<p data-js-guide-wizard-note></p>
		<button data-js-guide-wizard-back></button>
		<button data-js-guide-wizard-next disabled></button>
	</div>`;

function setup(): { root: HTMLElement; wizard: GuideWizard } {
	document.body.innerHTML = `
		<div data-js-guide-wizard='${JSON.stringify(strings)}' data-js-guide-wizard-title="Test guide">
			<span data-js-guide-wizard-progress-position></span>
			<span data-js-guide-wizard-progress-answered></span>
			<div data-js-guide-wizard-progress><div></div></div>
			<button data-js-guide-wizard-jump="0"></button>
			<button data-js-guide-wizard-jump="1"></button>
			<button data-js-guide-wizard-jump="summary"></button>
			${question(0, 'Size', option(0, 'small', 'pass') + option(0, 'large', 'stop', 'Too big') + option(0, 'unknown', 'review'))}
			${question(1, 'Height', option(1, 'low', 'pass') + option(1, 'high', 'stop'))}
			<section data-js-guide-wizard-result hidden>
				<div data-js-guide-wizard-hero="pass" tabindex="-1" hidden><strong data-js-guide-wizard-reason hidden></strong></div>
				<div data-js-guide-wizard-hero="stop" tabindex="-1" hidden><strong data-js-guide-wizard-reason hidden></strong></div>
				<div data-js-guide-wizard-hero="review" tabindex="-1" hidden><strong data-js-guide-wizard-reason hidden></strong></div>
				<div data-js-guide-wizard-remaining hidden><span data-js-guide-wizard-remaining-text></span></div>
				<dl data-js-guide-wizard-answers></dl>
				<p data-js-guide-wizard-created></p>
			</section>
		</div>`;
	const root = document.querySelector<HTMLElement>('[data-js-guide-wizard]') as HTMLElement;
	return { root, wizard: new GuideWizard(root) };
}

const choose = (root: HTMLElement, value: string) => {
	const input = root.querySelector<HTMLInputElement>(`input[value="${value}"]`) as HTMLInputElement;
	input.checked = true;
	input.dispatchEvent(new Event('change', { bubbles: true }));
};

const text = (root: HTMLElement, hook: string) => root.querySelector(`[data-js-guide-wizard-${hook}]`)?.textContent;

describe('outcome helpers', () => {
	it('falls back to pass for unknown outcomes', () => {
		expect(toOutcome('stop')).toBe('stop');
		expect(toOutcome('something')).toBe('pass');
		expect(toOutcome(undefined)).toBe('pass');
	});

	it('lets the most severe outcome win', () => {
		const input = (outcome: string, reason = '') => {
			const element = document.createElement('input');
			element.dataset.jsGuideWizardOutcome = outcome;
			element.dataset.jsGuideWizardReason = reason;
			return element;
		};
		expect(worstOutcome([input('pass'), input('review', 'r'), input('stop', 's')])).toEqual({
			outcome: 'stop',
			reason: 's',
		});
		expect(worstOutcome([input('pass'), input('review', 'r')])).toEqual({ outcome: 'review', reason: 'r' });
		expect(worstOutcome([])).toEqual({ outcome: 'pass', reason: '' });
	});
});

describe('GuideWizard', () => {
	it('starts on the first question with continue disabled', () => {
		const { root } = setup();

		expect(text(root, 'progress-position')).toBe('Question 1 of 2');
		expect(text(root, 'progress-answered')).toBe('0 answered');
		expect(text(root, 'note')).toBe('Choose one');
		expect(root.querySelector<HTMLButtonElement>('[data-js-guide-wizard-next]')?.disabled).toBe(true);
	});

	it('continues to the next question on a passing answer', () => {
		const { root, wizard } = setup();

		choose(root, 'small');
		expect(text(root, 'note')).toBe('Note pass');
		expect(text(root, 'progress-answered')).toBe('1 answered');
		wizard.commit(0);

		expect(wizard.currentView).toEqual({ kind: 'question', index: 1 });
		expect(text(root, 'progress-position')).toBe('Question 2 of 2');
	});

	it('stops with reason and remaining questions on a stop answer', () => {
		const { root, wizard } = setup();

		choose(root, 'large');
		wizard.commit(0);

		expect(wizard.currentResult).toEqual({ type: 'stop', reason: 'Too big', questionIndex: 0 });
		expect(root.querySelector<HTMLElement>('[data-js-guide-wizard-hero="stop"]')?.hidden).toBe(false);
		expect(root.querySelector('[data-js-guide-wizard-hero="stop"] [data-js-guide-wizard-reason]')?.textContent).toBe(
			'Too big',
		);
		expect(text(root, 'remaining-text')).toBe('1 left Stopped at Size.');
		expect(text(root, 'progress-position')).toBe('Summary');
	});

	it('shows the pass result after the last question', () => {
		const { root, wizard } = setup();

		choose(root, 'small');
		wizard.commit(0);
		choose(root, 'low');
		wizard.commit(1);

		expect(wizard.currentResult?.type).toBe('pass');
		expect(root.querySelector<HTMLElement>('[data-js-guide-wizard-remaining]')?.hidden).toBe(true);
		expect(root.querySelectorAll('.mod-guide-wizard__answer')).toHaveLength(2);
	});

	it('does not jump to questions that have not been reached', () => {
		const { wizard } = setup();

		wizard.goTo(1);

		expect(wizard.currentView).toEqual({ kind: 'question', index: 0 });
	});

	it('clears all answers on restart', () => {
		const { root, wizard } = setup();

		choose(root, 'large');
		wizard.commit(0);
		wizard.restart();

		expect(root.querySelectorAll('input:checked')).toHaveLength(0);
		expect(wizard.currentView).toEqual({ kind: 'question', index: 0 });
		expect(wizard.currentResult).toBeNull();
	});

	it('exports the answers as text', () => {
		const { root, wizard } = setup();

		choose(root, 'unknown');
		wizard.commit(0);
		const summary = wizard.exporter?.text(wizard.currentResult as NonNullable<typeof wizard.currentResult>) ?? '';

		expect(summary).toContain('TEST GUIDE');
		expect(summary).toContain('1. Size\r\n   - unknown\r\n   (Check)');
		expect(summary).toContain('2. Height\r\n   Not answered');
	});
});
