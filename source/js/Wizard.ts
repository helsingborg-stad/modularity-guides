declare const guides: Record<string, string> | undefined;

type Outcome = 'pass' | 'stop' | 'review';
type View = { kind: 'question'; index: number } | { kind: 'result' };

interface Result {
	type: Outcome;
	reason: string;
	questionIndex: number | null;
}

const OUTCOME_WEIGHT: Record<Outcome, number> = { pass: 0, review: 1, stop: 2 };

const ICONS: Record<Outcome, string> = {
	pass: '<path d="m5 12 4 4L19 6"/>',
	stop: '<path d="m6 6 12 12M18 6 6 18"/>',
	review: '<circle cx="12" cy="12" r="9"/><path d="M9 9a3 3 0 0 1 6 0c0 2-3 2-3 4M12 17v.2"/>',
};

const t = (key: string, ...values: (string | number)[]): string => {
	let text = (typeof guides !== 'undefined' && guides?.[key]) || key;
	values.forEach((value, i) => {
		text = text.replace(`%${i + 1}$s`, String(value)).replace('%s', String(value));
	});
	return text;
};

const svg = (outcome: Outcome) =>
	`<svg viewBox="0 0 24 24" class="mod-guide-wizard__svg" aria-hidden="true" focusable="false">${ICONS[outcome]}</svg>`;

const toOutcome = (value: string | null): Outcome => (value === 'stop' || value === 'review' ? value : 'pass');

export class GuideWizard {
	private readonly questions: HTMLElement[];
	private readonly result: HTMLElement;
	private readonly stepButtons: HTMLButtonElement[];
	private readonly summaryButton: HTMLButtonElement | null;
	private view: View = { kind: 'question', index: 0 };
	private furthest = 0;
	private lastResult: Result | null = null;
	private toastTimer: number | undefined;

	constructor(private readonly root: HTMLElement) {
		this.questions = [...root.querySelectorAll<HTMLElement>('[data-wizard-question]')];
		this.result = root.querySelector<HTMLElement>('[data-wizard-result]') as HTMLElement;
		this.stepButtons = [...root.querySelectorAll<HTMLButtonElement>('[data-wizard-jump]')].filter(
			(button) => button.dataset.wizardJump !== 'summary',
		);
		this.summaryButton = root.querySelector<HTMLButtonElement>('[data-wizard-jump="summary"]');

		if (this.questions.length === 0 || !this.result) {
			return;
		}

		this.bind();
		this.render(false);
	}

	/* ---------- State helpers ---------- */

	private inputs(index: number): HTMLInputElement[] {
		return [...(this.questions[index]?.querySelectorAll<HTMLInputElement>('input[data-wizard-outcome]') ?? [])];
	}

	private checked(index: number): HTMLInputElement[] {
		return this.inputs(index).filter((input) => input.checked);
	}

	private isAnswered(index: number): boolean {
		return this.checked(index).length > 0;
	}

	private outcomeOf(index: number): { outcome: Outcome; reason: string } {
		return this.checked(index).reduce(
			(worst, input) => {
				const outcome = toOutcome(input.dataset.wizardOutcome ?? null);
				return OUTCOME_WEIGHT[outcome] > OUTCOME_WEIGHT[worst.outcome]
					? { outcome, reason: input.dataset.wizardReason ?? '' }
					: worst;
			},
			{ outcome: 'pass' as Outcome, reason: '' },
		);
	}

	private shortTitle(index: number): string {
		return this.questions[index]?.dataset.wizardShortTitle ?? '';
	}

	/* ---------- Events ---------- */

	private bind(): void {
		this.questions.forEach((question, index) => {
			question.addEventListener('change', (event) => {
				const target = event.target as HTMLInputElement;
				if (target?.dataset?.wizardOutcome === undefined) {
					return;
				}
				this.updateQuestionControls(index, true);
				this.renderSidebar();
			});

			question.querySelector('[data-wizard-next]')?.addEventListener('click', () => this.commit(index));
			question.querySelector('[data-wizard-back]')?.addEventListener('click', () => this.goTo(index - 1));

			// Enter on an option continues, like submitting a form
			question.addEventListener('keydown', (event) => {
				const target = event.target as HTMLElement;
				if (event.key === 'Enter' && target instanceof HTMLInputElement && target.dataset.wizardOutcome) {
					event.preventDefault();
					if (this.isAnswered(index)) {
						this.commit(index);
					}
				}
			});
		});

		this.stepButtons.forEach((button) => {
			button.addEventListener('click', () => this.goTo(Number(button.dataset.wizardJump)));
		});
		this.summaryButton?.addEventListener('click', () => {
			if (this.lastResult) {
				this.view = { kind: 'result' };
				this.render();
			}
		});

		this.root.querySelector('[data-wizard-print]')?.addEventListener('click', () => window.print());
		this.root.querySelector('[data-wizard-download]')?.addEventListener('click', () => this.download());

		const dialog = this.root.querySelector<HTMLDialogElement>('[data-wizard-dialog]');
		this.root.querySelector('[data-wizard-restart]')?.addEventListener('click', () => {
			if (dialog?.showModal) {
				dialog.showModal();
			} else if (window.confirm(t('wizard_restart_title'))) {
				this.restart();
			}
		});
		dialog?.querySelector('[data-wizard-dialog-cancel]')?.addEventListener('click', () => dialog.close());
		dialog?.querySelector('[data-wizard-dialog-confirm]')?.addEventListener('click', () => {
			dialog.close();
			this.restart();
		});

		this.result.addEventListener('click', (event) => {
			const edit = (event.target as HTMLElement).closest<HTMLButtonElement>('[data-wizard-edit]');
			if (edit) {
				this.goTo(Number(edit.dataset.wizardEdit));
			}
		});
	}

	private commit(index: number): void {
		if (!this.isAnswered(index)) {
			return;
		}
		const { outcome, reason } = this.outcomeOf(index);

		if (outcome !== 'pass') {
			this.lastResult = { type: outcome, reason, questionIndex: index };
			this.view = { kind: 'result' };
		} else if (index === this.questions.length - 1) {
			this.lastResult = { type: 'pass', reason: '', questionIndex: null };
			this.view = { kind: 'result' };
		} else {
			this.lastResult = null;
			this.view = { kind: 'question', index: index + 1 };
			this.furthest = Math.max(this.furthest, index + 1);
		}
		this.render();
	}

	private goTo(index: number): void {
		if (!Number.isInteger(index) || index < 0 || index >= this.questions.length) {
			return;
		}
		if (index > this.furthest && !this.isAnswered(index)) {
			return;
		}
		this.view = { kind: 'question', index };
		this.render();
	}

	private restart(): void {
		this.questions.forEach((_, index) => {
			this.inputs(index).forEach((input) => {
				if (input.checked) {
					input.checked = false;
					input.dispatchEvent(new Event('change', { bubbles: true }));
				}
			});
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
			if (index === current) {
				this.updateQuestionControls(index, false);
			}
		});
		this.result.hidden = this.view.kind !== 'result';

		if (this.view.kind === 'result') {
			this.renderResult();
		}
		this.renderSidebar();

		if (focus) {
			const heading =
				this.view.kind === 'result'
					? this.result.querySelector<HTMLElement>('[data-wizard-hero]:not([hidden]) [data-wizard-hero-title]')
					: this.questions[current]?.querySelector<HTMLElement>('h2');
			heading?.focus({ preventScroll: true });

			const top = this.root.getBoundingClientRect().top;
			if (top < 0 || top > window.innerHeight * 0.4) {
				this.root.scrollIntoView({ behavior: 'smooth', block: 'start' });
			}
		}
	}

	private updateQuestionControls(index: number, changed: boolean): void {
		const question = this.questions[index];
		const next = question.querySelector<HTMLButtonElement>('[data-wizard-next]');
		const note = question.querySelector<HTMLElement>('[data-wizard-note]');
		const answered = this.isAnswered(index);

		if (next) {
			next.disabled = !answered;
			if (answered) {
				next.removeAttribute('disabled');
			}
		}

		if (!note) {
			return;
		}
		if (!answered) {
			note.textContent = t(question.dataset.wizardType === 'radio' ? 'wizard_choose_one' : 'wizard_choose_many');
		} else if (!changed) {
			note.textContent = t('wizard_saved_answer');
		} else {
			note.textContent = t(`wizard_note_${this.outcomeOf(index).outcome}`);
		}
	}

	private renderSidebar(): void {
		const total = this.questions.length;
		const answered = this.questions.filter((_, i) => this.isAnswered(i)).length;
		const current = this.view.kind === 'question' ? this.view.index : -1;

		const position = this.root.querySelector('[data-wizard-progress-position]');
		if (position) {
			position.textContent =
				this.view.kind === 'result' ? t('wizard_summary') : t('wizard_question_x_of_y', current + 1, total);
		}
		const count = this.root.querySelector('[data-wizard-progress-answered]');
		if (count) {
			count.textContent = t(answered === 1 ? 'wizard_answered_one' : 'wizard_answered_many', answered);
		}
		const progress = this.root.querySelector<HTMLProgressElement>('[data-wizard-progress]');
		if (progress) {
			progress.value = answered;
		}

		this.stepButtons.forEach((button, index) => {
			const isAnswered = this.isAnswered(index);
			const dot = button.querySelector<HTMLElement>('[data-wizard-step-dot]');
			const outcome = this.outcomeOf(index).outcome;

			button.disabled = index > this.furthest && !isAnswered;
			button.classList.toggle('is-completed', isAnswered);
			button.classList.toggle('is-stop', isAnswered && outcome === 'stop');
			button.classList.toggle('is-review', isAnswered && outcome === 'review');
			if (index === current) {
				button.setAttribute('aria-current', 'step');
			} else {
				button.removeAttribute('aria-current');
			}
			if (dot) {
				dot.innerHTML = isAnswered ? svg(outcome) : String(index + 1);
			}
		});

		if (this.summaryButton) {
			this.summaryButton.disabled = !this.lastResult;
			if (this.view.kind === 'result') {
				this.summaryButton.setAttribute('aria-current', 'step');
			} else {
				this.summaryButton.removeAttribute('aria-current');
			}
		}
	}

	private renderResult(): void {
		const result = this.lastResult ?? { type: 'pass', reason: '', questionIndex: null };

		this.result.querySelectorAll<HTMLElement>('[data-wizard-hero]').forEach((hero) => {
			const active = hero.dataset.wizardHero === result.type;
			hero.hidden = !active;
			const reason = hero.querySelector<HTMLElement>('[data-wizard-reason]');
			if (reason) {
				reason.textContent = active ? result.reason : '';
				reason.hidden = !active || result.reason === '';
			}
		});

		const remaining = this.questions.filter((_, i) => !this.isAnswered(i)).length;
		const remainingNotice = this.result.querySelector<HTMLElement>('[data-wizard-remaining]');
		if (remainingNotice) {
			const show = result.questionIndex !== null && remaining > 0;
			remainingNotice.hidden = !show;
			remainingNotice.textContent = show
				? `${t(remaining === 1 ? 'wizard_remaining_one' : 'wizard_remaining_many', remaining)} ${t(
						'wizard_stopped_at',
						this.shortTitle(result.questionIndex as number),
					)}`
				: '';
		}

		const answers = this.result.querySelector<HTMLElement>('[data-wizard-answers]');
		if (answers) {
			answers.replaceChildren(...this.questions.map((_, index) => this.answerRow(index)));
		}

		const created = this.result.querySelector<HTMLElement>('[data-wizard-created]');
		if (created) {
			created.textContent = `${t('wizard_created')} ${this.formatDate()}.`;
		}
	}

	private answerRow(index: number): HTMLElement {
		const row = document.createElement('div');
		const answered = this.isAnswered(index);
		row.className = `mod-guide-wizard__answer${answered ? '' : ' is-unanswered'}`;

		const dt = document.createElement('dt');
		const label = document.createElement('span');
		label.textContent = `${index + 1}. ${this.shortTitle(index)}`;
		dt.append(label);

		if (answered || index <= this.furthest) {
			const edit = document.createElement('button');
			edit.type = 'button';
			edit.className = 'mod-guide-wizard__edit';
			edit.dataset.wizardEdit = String(index);
			edit.textContent = t('wizard_edit');
			const sr = document.createElement('span');
			sr.className = 'u-sr__only';
			sr.textContent = ` ${this.shortTitle(index)}`;
			edit.append(sr);
			dt.append(edit);
		}

		const dd = document.createElement('dd');
		const checked = this.checked(index);
		if (checked.length === 0) {
			dd.textContent = t('wizard_not_answered');
		} else if (checked.length === 1) {
			dd.textContent = checked[0].dataset.wizardLabel ?? '';
		} else {
			const list = document.createElement('ul');
			checked.forEach((input) => {
				const li = document.createElement('li');
				li.textContent = input.dataset.wizardLabel ?? '';
				list.append(li);
			});
			dd.append(list);
		}

		const { outcome } = this.outcomeOf(index);
		if (answered && outcome !== 'pass') {
			const tag = document.createElement('span');
			tag.className = `mod-guide-wizard__outcome mod-guide-wizard__outcome--${outcome}`;
			tag.textContent = t(`wizard_outcome_${outcome}`);
			dd.append(tag);
		}

		row.append(dt, dd);
		return row;
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
		const hero = this.result.querySelector<HTMLElement>(`[data-wizard-hero="${result.type}"]`);
		const clean = (text: string | null | undefined) => (text ?? '').replace(/\s+/g, ' ').trim();
		const lines: string[] = [
			clean(this.root.dataset.guideTitle).toUpperCase(),
			`${t('wizard_created')} ${this.formatDate()}`,
			'',
			clean(hero?.querySelector('[data-wizard-hero-title]')?.textContent),
		];
		if (result.reason) {
			lines.push(result.reason);
		}
		lines.push(
			clean(hero?.querySelector('[data-wizard-hero-text]')?.textContent),
			'',
			t('wizard_your_answers').toUpperCase(),
		);

		this.questions.forEach((_, index) => {
			const checked = this.checked(index);
			lines.push(`${index + 1}. ${this.shortTitle(index)}`);
			if (checked.length === 0) {
				lines.push(`   ${t('wizard_not_answered')}`);
			}
			for (const input of checked) {
				lines.push(`   - ${input.dataset.wizardLabel ?? ''}`);
			}
			const { outcome } = this.outcomeOf(index);
			if (checked.length > 0 && outcome !== 'pass') {
				lines.push(`   (${t(`wizard_outcome_${outcome}`)})`);
			}
			lines.push('');
		});

		// Visible summary content, e.g. a filtered todo list
		this.result.querySelectorAll<HTMLElement>('.mod-guide-wizard__panel').forEach((panel, i) => {
			if (i === 0) {
				return;
			}
			const heading = clean(panel.querySelector('h3')?.textContent);
			const items: string[] = [];
			panel.querySelectorAll<HTMLElement>('h4, label, li, p').forEach((el) => {
				if (el.closest('.c-modal, dialog, form') || !el.checkVisibility?.()) {
					return;
				}
				const text = clean(el.textContent);
				if (text) {
					items.push(el.tagName === 'H4' ? `\n${text}` : `   - ${text}`);
				}
			});
			if (items.length > 0) {
				lines.push(heading.toUpperCase(), ...items, '');
			}
		});

		const notice = clean(
			this.result.querySelector('.mod-guide-wizard__notice:not([data-wizard-remaining]) p')?.textContent,
		);
		if (notice) {
			lines.push(notice, '');
		}
		lines.push(window.location.href);

		return lines.join('\r\n');
	}

	private download(): void {
		const blob = new Blob([`﻿${this.summaryText()}`], { type: 'text/plain;charset=utf-8' });
		const url = URL.createObjectURL(blob);
		const link = document.createElement('a');
		const name = (this.root.dataset.guideTitle || 'guide')
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
		this.toast(t('wizard_download_done'));
	}

	private toast(text: string): void {
		const toast = this.root.querySelector<HTMLElement>('[data-wizard-toast]');
		if (!toast) {
			return;
		}
		toast.textContent = text;
		toast.hidden = false;
		window.clearTimeout(this.toastTimer);
		this.toastTimer = window.setTimeout(() => {
			toast.hidden = true;
		}, 4500);
	}
}

export function initWizards(): void {
	document.querySelectorAll<HTMLElement>('.js-mod-guide-wizard').forEach((root) => {
		new GuideWizard(root);
	});
}

window.addEventListener('DOMContentLoaded', initWizards);
