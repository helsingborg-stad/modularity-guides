import { Answers } from './Answers';
import { Conditions } from './Conditions';

const option = (question: number, value: string, showWhen = '') => `
	<div class="mod-guide-wizard__option">
		<input type="radio" name="q${question}" value="${value}" data-js-guide-wizard-outcome="pass"
			${showWhen !== '' ? `data-js-guide-wizard-show-when="${showWhen}"` : ''}>
	</div>`;

function setup(): { answers: Answers; conditions: Conditions } {
	document.body.innerHTML = `
		<div data-js-guide-wizard-question="0">${option(0, 'inside')}${option(0, 'outside')}</div>
		<div data-js-guide-wizard-question="1">
			${option(1, 'low')}${option(1, 'over-4', 'inside')}${option(1, 'over-4-5', 'outside, inside')}
		</div>`;
	const answers = new Answers([...document.querySelectorAll<HTMLElement>('[data-js-guide-wizard-question]')]);
	return { answers, conditions: new Conditions(answers) };
}

const input = (value: string) => document.querySelector<HTMLInputElement>(`input[value="${value}"]`) as HTMLInputElement;
const isShown = (value: string) => !(input(value).closest('.mod-guide-wizard__option') as HTMLElement).hidden;

describe('Conditions', () => {
	it('hides options until a matching earlier answer is chosen', () => {
		const { conditions } = setup();
		conditions.apply();

		expect(isShown('low')).toBe(true);
		expect(isShown('over-4')).toBe(false);
		expect(input('over-4').disabled).toBe(true);

		input('outside').checked = true;
		conditions.apply();

		expect(isShown('over-4')).toBe(false);
		expect(isShown('over-4-5')).toBe(true);
		expect(input('over-4-5').disabled).toBe(false);
	});

	it('unchecks an answer whose option is hidden by a changed earlier answer', () => {
		const { answers, conditions } = setup();
		input('inside').checked = true;
		conditions.apply();
		input('over-4').checked = true;
		const changed = jest.fn();
		input('over-4').addEventListener('change', changed);

		input('inside').checked = false;
		input('outside').checked = true;
		conditions.apply();

		expect(input('over-4').checked).toBe(false);
		expect(answers.isAnswered(1)).toBe(false);
		expect(changed).toHaveBeenCalledTimes(1);
	});

	it('only looks at earlier questions', () => {
		document.body.innerHTML = `
			<div data-js-guide-wizard-question="0">${option(0, 'first', 'later')}</div>
			<div data-js-guide-wizard-question="1">${option(1, 'later')}</div>`;
		const answers = new Answers([...document.querySelectorAll<HTMLElement>('[data-js-guide-wizard-question]')]);
		input('later').checked = true;
		new Conditions(answers).apply();

		expect(isShown('first')).toBe(false);
	});
});
