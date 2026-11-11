import type { Answers } from './Answers';
import { hook, hookAttribute } from './hooks';
import { LABEL_KEY, type Result } from './outcome';
import { formatDate, setText, type WizardStrings } from './strings';

/**
 * The summary page: result notice, remaining questions and the list of answers.
 */
export class Summary {
	constructor(
		private readonly element: HTMLElement,
		private readonly answers: Answers,
		private readonly strings: WizardStrings,
	) {}

	set hidden(hidden: boolean) {
		this.element.hidden = hidden;
	}

	/** The visible result notice, which receives focus when the summary opens. */
	get focusTarget(): HTMLElement | null {
		return this.element.querySelector<HTMLElement>(`${hook('hero')}:not([hidden])`);
	}

	onEdit(goTo: (index: number) => void): void {
		this.element.addEventListener('click', (event) => {
			const edit = (event.target as HTMLElement).closest<HTMLButtonElement>(hook('edit'));
			if (edit) {
				goTo(Number(edit.dataset.jsGuideWizardEdit));
			}
		});
	}

	render(result: Result, furthest: number): void {
		this.renderHero(result);
		this.renderRemaining(result);
		this.element
			.querySelector<HTMLElement>(hook('answers'))
			?.replaceChildren(...Array.from({ length: this.answers.count }, (_, index) => this.answerRow(index, furthest)));
		setText(this.element.querySelector(hook('created')), `${this.strings.created} ${formatDate()}.`);
	}

	private renderHero(result: Result): void {
		this.element.querySelectorAll<HTMLElement>(hook('hero')).forEach((hero) => {
			const active = hero.dataset.jsGuideWizardHero === result.type;
			hero.hidden = !active;
			const reason = hero.querySelector<HTMLElement>(hook('reason'));
			if (reason) {
				reason.textContent = active ? result.reason : '';
				reason.hidden = !active || result.reason === '';
			}
		});
	}

	private renderRemaining(result: Result): void {
		const notice = this.element.querySelector<HTMLElement>(hook('remaining'));
		if (!notice) {
			return;
		}
		const remaining = this.answers.count - this.answers.answeredCount();
		const show = result.questionIndex !== null && remaining > 0;

		notice.hidden = !show;
		setText(
			notice.querySelector(hook('remaining-text')),
			show ? `${this.strings.remaining[remaining]} ${this.strings.stoppedAt[result.questionIndex as number]}` : '',
		);
	}

	private answerRow(index: number, furthest: number): HTMLElement {
		const labels = this.answers.labels(index);
		const answered = labels.length > 0;
		const row = document.createElement('div');
		row.className = `mod-guide-wizard__answer${answered ? '' : ' is-unanswered'}`;

		const dt = document.createElement('dt');
		const number = document.createElement('span');
		number.className = 'mod-guide-wizard__answer-number';
		number.setAttribute('aria-hidden', 'true');
		number.textContent = String(index + 1);
		const title = document.createElement('span');
		title.className = 'mod-guide-wizard__answer-title';
		title.textContent = this.answers.shortTitle(index);
		dt.append(number, title);

		const dd = this.answerValue(index, labels);
		if (answered || index <= furthest) {
			dd.append(this.editButton(index));
		}

		row.append(dt, dd);
		return row;
	}

	private answerValue(index: number, labels: string[]): HTMLElement {
		const dd = document.createElement('dd');
		const list = document.createElement('ul');
		list.className = 'mod-guide-wizard__answer-list';

		if (labels.length === 0) {
			list.append(this.chip(this.strings.notAnswered, 'mod-guide-wizard__chip mod-guide-wizard__chip--empty'));
			dd.append(list);
			return dd;
		}

		list.append(...labels.map((label) => this.chip(label, 'mod-guide-wizard__chip')));

		const { outcome } = this.answers.outcomeOf(index);
		if (outcome !== 'pass') {
			list.append(
				this.chip(
					this.strings[LABEL_KEY[outcome]] as string,
					`mod-guide-wizard__outcome mod-guide-wizard__outcome--${outcome}`,
				),
			);
		}
		dd.append(list);
		return dd;
	}

	private chip(text: string, className: string): HTMLLIElement {
		const item = document.createElement('li');
		item.className = className;
		item.textContent = text;
		return item;
	}

	private editButton(index: number): HTMLButtonElement {
		const edit = document.createElement('button');
		edit.type = 'button';
		edit.className = 'mod-guide-wizard__edit';
		edit.setAttribute(hookAttribute('edit'), String(index));
		edit.textContent = this.strings.edit;
		const hidden = document.createElement('span');
		hidden.className = 'u-sr__only';
		hidden.textContent = ` ${this.answers.shortTitle(index)}`;
		edit.append(hidden);
		return edit;
	}
}
