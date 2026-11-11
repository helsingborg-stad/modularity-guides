/**
 * Step by step (wizard) display mode.
 *
 * All texts come from the view model (Helper\Wizard) through the data-js-guide-wizard attribute.
 * Texts containing numbers are prepared server side for every possible value, so plural forms
 * are handled by WordPress and the script never formats strings.
 */

export type Outcome = 'pass' | 'stop' | 'review';
type View = { kind: 'question'; index: number } | { kind: 'result' };

export interface WizardStrings {
	answered: string[];
	remaining: string[];
	position: string[];
	stoppedAt: string[];
	summary: string;
	chooseOne: string;
	chooseMany: string;
	savedAnswer: string;
	notePass: string;
	noteStop: string;
	noteReview: string;
	yourAnswers: string;
	edit: string;
	notAnswered: string;
	outcomeStop: string;
	outcomeReview: string;
	created: string;
	downloadDone: string;
}

interface Result {
	type: Outcome;
	reason: string;
	questionIndex: number | null;
}

const ROOT = 'data-js-guide-wizard';
const hook = (name: string) => `[${ROOT}-${name}]`;
const OUTCOME_WEIGHT: Record<Outcome, number> = { pass: 0, review: 1, stop: 2 };
const NOTE: Record<Outcome, keyof WizardStrings> = { pass: 'notePass', stop: 'noteStop', review: 'noteReview' };
const OUTCOME_LABEL: Record<Exclude<Outcome, 'pass'>, keyof WizardStrings> = {
	stop: 'outcomeStop',
	review: 'outcomeReview',
};

export const toOutcome = (value: string | null | undefined): Outcome =>
	value === 'stop' || value === 'review' ? value : 'pass';

/**
 * The most severe outcome among the given inputs wins (stop > review > pass).
 */
export function worstOutcome(inputs: HTMLInputElement[]): { outcome: Outcome; reason: string } {
	return inputs.reduce(
		(worst, input) => {
			const outcome = toOutcome(input.dataset.jsGuideWizardOutcome);
			return OUTCOME_WEIGHT[outcome] > OUTCOME_WEIGHT[worst.outcome]
				? { outcome, reason: input.dataset.jsGuideWizardReason ?? '' }
				: worst;
		},
		{ outcome: 'pass' as Outcome, reason: '' },
	);
}

export class GuideWizard {
	private readonly strings: WizardStrings;
	private readonly questions: HTMLElement[];
	private readonly result: HTMLElement;
	private readonly stepButtons: HTMLButtonElement[];
	private readonly summaryButton: HTMLButtonElement | null;
	private view: View = { kind: 'question', index: 0 };
	private furthest = 0;
	private lastResult: Result | null = null;
	private toastTimer: number | undefined;

	constructor(private readonly root: HTMLElement) {
		this.strings = JSON.parse(root.getAttribute(ROOT) || '{}') as WizardStrings;
		this.questions = [...root.querySelectorAll<HTMLElement>(hook('question'))];
		this.result = root.querySelector<HTMLElement>(hook('result')) as HTMLElement;
		this.stepButtons = [...root.querySelectorAll<HTMLButtonElement>(hook('jump'))].filter(
			(button) => button.dataset.jsGuideWizardJump !== 'summary',
		);
		this.summaryButton = root.querySelector<HTMLButtonElement>(`[${ROOT}-jump="summary"]`);

		if (this.questions.length === 0 || !this.result) {
			return;
		}

		this.bind();
		this.render(false);
	}

	/* ---------- State ---------- */

	public get currentView(): View {
		return this.view;
	}

	public get currentResult(): Result | null {
		return this.lastResult;
	}

	private inputs(index: number): HTMLInputElement[] {
		return [...(this.questions[index]?.querySelectorAll<HTMLInputElement>(hook('outcome')) ?? [])];
	}

	private checked(index: number): HTMLInputElement[] {
		return this.inputs(index).filter((input) => input.checked);
	}

	private isAnswered(index: number): boolean {
		return this.checked(index).length > 0;
	}

	private outcomeOf(index: number): { outcome: Outcome; reason: string } {
		return worstOutcome(this.checked(index));
	}

	private shortTitle(index: number): string {
		return this.questions[index]?.dataset.jsGuideWizardShortTitle ?? '';
	}

	/* ---------- Events ---------- */

	private bind(): void {
		this.questions.forEach((question, index) => {
			question.addEventListener('change', (event) => {
				if ((event.target as HTMLInputElement)?.dataset?.jsGuideWizardOutcome === undefined) {
					return;
				}
				this.updateQuestionControls(index, true);
				this.renderSidebar();
			});
			question.querySelector(hook('next'))?.addEventListener('click', () => this.commit(index));
			question.querySelector(hook('back'))?.addEventListener('click', () => this.goTo(index - 1));
		});

		this.stepButtons.forEach((button) => {
			button.addEventListener('click', () => this.goTo(Number(button.dataset.jsGuideWizardJump)));
		});
		this.summaryButton?.addEventListener('click', () => this.showResult());

		this.root.querySelector(hook('print'))?.addEventListener('click', () => window.print());
		this.root.querySelector(hook('download'))?.addEventListener('click', () => this.download());
		// The restart modal itself is opened and closed by the component library (data-open / data-close)
		this.root.querySelector(hook('restart'))?.addEventListener('click', () => this.restart());

		this.result.addEventListener('click', (event) => {
			const edit = (event.target as HTMLElement).closest<HTMLButtonElement>(hook('edit'));
			if (edit) {
				this.goTo(Number(edit.dataset.jsGuideWizardEdit));
			}
		});
	}

	public commit(index: number): void {
		if (!this.isAnswered(index)) {
			return;
		}
		const { outcome, reason } = this.outcomeOf(index);
		const isLast = index === this.questions.length - 1;

		if (outcome !== 'pass' || isLast) {
			this.lastResult = { type: outcome, reason, questionIndex: outcome === 'pass' ? null : index };
			this.view = { kind: 'result' };
			this.render();
			return;
		}

		this.lastResult = null;
		this.furthest = Math.max(this.furthest, index + 1);
		this.view = { kind: 'question', index: index + 1 };
		this.render();
	}

	public goTo(index: number): void {
		const outOfRange = !Number.isInteger(index) || index < 0 || index >= this.questions.length;
		if (outOfRange || (index > this.furthest && !this.isAnswered(index))) {
			return;
		}
		this.view = { kind: 'question', index };
		this.render();
	}

	private showResult(): void {
		if (this.lastResult) {
			this.view = { kind: 'result' };
			this.render();
		}
	}

	public restart(): void {
		this.questions.forEach((_, index) => {
			for (const input of this.checked(index)) {
				input.checked = false;
				input.dispatchEvent(new Event('change', { bubbles: true }));
			}
		});
		this.furthest = 0;
		this.lastResult = null;
		this.view = { kind: 'question', index: 0 };
		this.render();
	}

	/* ---------- Rendering ---------- */

	private render(focus = true): void {
		const current = this.view.kind === 'question' ? this.view.index : -1;

		this.questions.forEach((question, index) => {
			question.hidden = index !== current;
		});
		if (current >= 0) {
			this.updateQuestionControls(current, false);
		}
		this.result.hidden = this.view.kind !== 'result';
		if (this.view.kind === 'result') {
			this.renderResult();
		}
		this.renderSidebar();

		if (focus) {
			this.moveFocus(current);
		}
	}

	private moveFocus(current: number): void {
		const target =
			this.view.kind === 'result'
				? this.result.querySelector<HTMLElement>(`${hook('hero')}:not([hidden])`)
				: this.questions[current]?.querySelector<HTMLElement>('h2');
		target?.focus({ preventScroll: true });

		const top = this.root.getBoundingClientRect().top;
		if (top < 0 || top > window.innerHeight * 0.4) {
			this.root.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
		}
	}

	private updateQuestionControls(index: number, changed: boolean): void {
		const question = this.questions[index];
		const next = question.querySelector<HTMLButtonElement>(hook('next'));
		const note = question.querySelector<HTMLElement>(hook('note'));
		const answered = this.isAnswered(index);

		if (next) {
			next.disabled = !answered;
		}
		if (!note) {
			return;
		}

		let text = this.strings[NOTE[this.outcomeOf(index).outcome]] as string;
		if (!answered) {
			text = question.dataset.jsGuideWizardType === 'radio' ? this.strings.chooseOne : this.strings.chooseMany;
		} else if (!changed) {
			text = this.strings.savedAnswer;
		}
		note.textContent = text;
	}

	private renderSidebar(): void {
		const answered = this.questions.filter((_, i) => this.isAnswered(i)).length;
		const current = this.view.kind === 'question' ? this.view.index : -1;

		this.setText(
			this.root.querySelector(hook('progress-position')),
			current >= 0 ? this.strings.position[current] : this.strings.summary,
		);
		this.setText(this.root.querySelector(hook('progress-answered')), this.strings.answered[answered]);

		const progress = this.root.querySelector<HTMLElement>(`${hook('progress')} > *`);
		if (progress) {
			progress.style.width = `${(answered / this.questions.length) * 100}%`;
		}

		this.stepButtons.forEach((button, index) => {
			this.renderStepButton(button, index, current);
		});

		if (this.summaryButton) {
			this.summaryButton.disabled = !this.lastResult;
			this.setCurrent(this.summaryButton, this.view.kind === 'result');
		}
	}

	private renderStepButton(button: HTMLButtonElement, index: number, current: number): void {
		const isAnswered = this.isAnswered(index);
		const outcome = isAnswered ? this.outcomeOf(index).outcome : null;

		button.disabled = index > this.furthest && !isAnswered;
		button.classList.toggle('is-completed', isAnswered);
		for (const state of ['pass', 'stop', 'review'] as Outcome[]) {
			button.classList.toggle(`is-${state}`, outcome === state);
		}
		this.setCurrent(button, index === current);
	}

	private setCurrent(element: HTMLElement, isCurrent: boolean): void {
		if (isCurrent) {
			element.setAttribute('aria-current', 'step');
			return;
		}
		element.removeAttribute('aria-current');
	}

	private renderResult(): void {
		const result = this.lastResult ?? { type: 'pass' as Outcome, reason: '', questionIndex: null };

		this.result.querySelectorAll<HTMLElement>(hook('hero')).forEach((hero) => {
			const active = hero.dataset.jsGuideWizardHero === result.type;
			hero.hidden = !active;
			const reason = hero.querySelector<HTMLElement>(hook('reason'));
			if (reason) {
				reason.textContent = active ? result.reason : '';
				reason.hidden = !active || result.reason === '';
			}
		});

		this.renderRemaining(result);

		this.result
			.querySelector<HTMLElement>(hook('answers'))
			?.replaceChildren(...this.questions.map((_, index) => this.answerRow(index)));

		this.setText(this.result.querySelector(hook('created')), `${this.strings.created} ${this.formatDate()}.`);
	}

	private renderRemaining(result: Result): void {
		const notice = this.result.querySelector<HTMLElement>(hook('remaining'));
		if (!notice) {
			return;
		}
		const remaining = this.questions.filter((_, i) => !this.isAnswered(i)).length;
		const show = result.questionIndex !== null && remaining > 0;

		notice.hidden = !show;
		this.setText(
			notice.querySelector(hook('remaining-text')),
			show ? `${this.strings.remaining[remaining]} ${this.strings.stoppedAt[result.questionIndex as number]}` : '',
		);
	}

	private answerRow(index: number): HTMLElement {
		const row = document.createElement('div');
		const checked = this.checked(index);
		const answered = checked.length > 0;
		row.className = `mod-guide-wizard__answer${answered ? '' : ' is-unanswered'}`;

		const dt = document.createElement('dt');
		const label = document.createElement('span');
		label.textContent = `${index + 1}. ${this.shortTitle(index)}`;
		dt.append(label);
		if (answered || index <= this.furthest) {
			dt.append(this.editButton(index));
		}

		const dd = document.createElement('dd');
		if (checked.length === 1) {
			dd.textContent = checked[0].dataset.jsGuideWizardLabel ?? '';
		} else if (answered) {
			const list = document.createElement('ul');
			for (const input of checked) {
				const li = document.createElement('li');
				li.textContent = input.dataset.jsGuideWizardLabel ?? '';
				list.append(li);
			}
			dd.append(list);
		} else {
			dd.textContent = this.strings.notAnswered;
		}

		const { outcome } = this.outcomeOf(index);
		if (answered && outcome !== 'pass') {
			const tag = document.createElement('span');
			tag.className = `mod-guide-wizard__outcome mod-guide-wizard__outcome--${outcome}`;
			tag.textContent = this.strings[OUTCOME_LABEL[outcome]] as string;
			dd.append(tag);
		}

		row.append(dt, dd);
		return row;
	}

	private editButton(index: number): HTMLButtonElement {
		const edit = document.createElement('button');
		edit.type = 'button';
		edit.className = 'mod-guide-wizard__edit';
		edit.setAttribute(`${ROOT}-edit`, String(index));
		edit.textContent = this.strings.edit;
		const hidden = document.createElement('span');
		hidden.className = 'u-sr__only';
		hidden.textContent = ` ${this.shortTitle(index)}`;
		edit.append(hidden);
		return edit;
	}

	private setText(element: Element | null, text: string | undefined): void {
		if (element) {
			element.textContent = text ?? '';
		}
	}

	private formatDate(): string {
		try {
			return new Intl.DateTimeFormat(document.documentElement.lang || undefined, {
				dateStyle: 'long',
				timeStyle: 'short',
			}).format(new Date());
		} catch {
			return new Date().toLocaleString();
		}
	}

	/* ---------- Export ---------- */

	public summaryText(): string {
		const result = this.lastResult ?? { type: 'pass' as Outcome, reason: '', questionIndex: null };
		const hero = this.result.querySelector<HTMLElement>(`[${ROOT}-hero="${result.type}"]`);
		const clean = (text: string | null | undefined) => (text ?? '').replace(/\s+/g, ' ').trim();

		const lines: string[] = [
			clean(this.root.getAttribute(`${ROOT}-title`)).toUpperCase(),
			`${this.strings.created} ${this.formatDate()}`,
			'',
			clean(hero?.querySelector('.c-notice__title')?.textContent),
		];
		if (result.reason) {
			lines.push(result.reason);
		}
		lines.push(clean(hero?.querySelector(hook('hero-text'))?.textContent), '', this.strings.yourAnswers.toUpperCase());

		this.questions.forEach((_, index) => {
			lines.push(...this.answerLines(index), '');
		});

		// Visible summary content, e.g. a filtered todo list
		this.result.querySelectorAll<HTMLElement>(`[${ROOT}-panel="content"]`).forEach((panel) => {
			const items = [...panel.querySelectorAll<HTMLElement>('h4, label, li, p')]
				.filter((el) => !el.closest('.c-modal, dialog, form') && el.checkVisibility?.() !== false)
				.map((el) => ({ tag: el.tagName, text: clean(el.textContent) }))
				.filter((item) => item.text !== '')
				.map((item) => (item.tag === 'H4' ? `\n${item.text}` : `   - ${item.text}`));
			if (items.length > 0) {
				lines.push(clean(panel.querySelector('h3')?.textContent).toUpperCase(), ...items, '');
			}
		});

		const disclaimer = clean(this.result.querySelector(`${hook('disclaimer')} [data-js-notice-message]`)?.textContent);
		if (disclaimer) {
			lines.push(disclaimer, '');
		}
		lines.push(window.location.href);

		return lines.join('\r\n');
	}

	private answerLines(index: number): string[] {
		const checked = this.checked(index);
		const lines = [`${index + 1}. ${this.shortTitle(index)}`];

		if (checked.length === 0) {
			return [...lines, `   ${this.strings.notAnswered}`];
		}
		for (const input of checked) {
			lines.push(`   - ${input.dataset.jsGuideWizardLabel ?? ''}`);
		}
		const { outcome } = this.outcomeOf(index);
		if (outcome !== 'pass') {
			lines.push(`   (${this.strings[OUTCOME_LABEL[outcome]]})`);
		}
		return lines;
	}

	private download(): void {
		const blob = new Blob([`﻿${this.summaryText()}`], { type: 'text/plain;charset=utf-8' });
		const url = URL.createObjectURL(blob);
		const link = document.createElement('a');
		const name = (this.root.getAttribute(`${ROOT}-title`) || 'guide')
			.toLowerCase()
			.normalize('NFD')
			.replace(/[̀-ͯ]/g, '')
			.replace(/[^a-z0-9]+/g, '-')
			.replace(/^-|-$/g, '');
		link.href = url;
		link.download = `${name || 'guide'}.txt`;
		document.body.append(link);
		link.click();
		link.remove();
		window.setTimeout(() => URL.revokeObjectURL(url), 30000);
		this.toast(this.strings.downloadDone);
	}

	private toast(text: string): void {
		const toast = this.root.querySelector<HTMLElement>(hook('toast'));
		if (!toast) {
			return;
		}
		this.setText(toast.querySelector(hook('toast-text')), text);
		toast.hidden = false;
		window.clearTimeout(this.toastTimer);
		this.toastTimer = window.setTimeout(() => {
			toast.hidden = true;
		}, 4500);
	}
}

export function initWizards(): GuideWizard[] {
	return [...document.querySelectorAll<HTMLElement>(`[${ROOT}]`)].map((root) => new GuideWizard(root));
}

window.addEventListener('DOMContentLoaded', () => {
	initWizards();
});
