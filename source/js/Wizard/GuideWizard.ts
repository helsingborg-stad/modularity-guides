import { Answers } from './Answers';
import { hook, ROOT } from './hooks';
import { NOTE_KEY, type Result } from './outcome';
import { Sidebar } from './Sidebar';
import { parseStrings, type WizardStrings } from './strings';
import { Summary } from './Summary';
import { TextExport } from './TextExport';
import { Toast } from './Toast';

type View = { kind: 'question'; index: number } | { kind: 'result' };

/**
 * Step by step (wizard) display mode: navigation between questions and the summary.
 */
export class GuideWizard {
	private readonly strings: WizardStrings;
	private readonly answers: Answers;
	private readonly sidebar: Sidebar;
	private readonly summary: Summary | null = null;
	private readonly textExport: TextExport | null = null;
	private readonly toast: Toast;
	private view: View = { kind: 'question', index: 0 };
	private furthest = 0;
	private lastResult: Result | null = null;

	constructor(private readonly root: HTMLElement) {
		this.strings = parseStrings(root.getAttribute(ROOT));
		this.answers = new Answers([...root.querySelectorAll<HTMLElement>(hook('question'))]);
		this.sidebar = new Sidebar(root, this.answers, this.strings);
		this.toast = new Toast(root.querySelector<HTMLElement>(hook('toast')));

		const summary = root.querySelector<HTMLElement>(hook('result'));
		if (this.answers.count === 0 || !summary) {
			return;
		}
		this.summary = new Summary(summary, this.answers, this.strings);
		this.textExport = new TextExport(root, summary, this.answers, this.strings);

		this.bind();
		this.render(false);
	}

	public get currentView(): View {
		return this.view;
	}

	public get currentResult(): Result | null {
		return this.lastResult;
	}

	public get exporter(): TextExport | null {
		return this.textExport;
	}

	private bind(): void {
		for (let index = 0; index < this.answers.count; index++) {
			const question = this.answers.question(index) as HTMLElement;
			question.addEventListener('change', (event) => {
				if ((event.target as HTMLInputElement)?.dataset?.jsGuideWizardOutcome === undefined) {
					return;
				}
				this.updateQuestionControls(index, true);
				this.renderSidebar();
			});
			question.querySelector(hook('next'))?.addEventListener('click', () => this.commit(index));
			question.querySelector(hook('back'))?.addEventListener('click', () => this.goTo(index - 1));
		}

		this.sidebar.onJump(
			(index) => this.goTo(index),
			() => this.showResult(),
		);
		this.summary?.onEdit((index) => this.goTo(index));

		this.root.querySelector(hook('print'))?.addEventListener('click', () => window.print());
		this.root.querySelector(hook('download'))?.addEventListener('click', () => this.download());
		// The restart modal itself is opened and closed by the component library (data-open / data-close)
		this.root.querySelector(hook('restart'))?.addEventListener('click', () => this.restart());
	}

	public commit(index: number): void {
		if (!this.answers.isAnswered(index)) {
			return;
		}
		const { outcome, reason } = this.answers.outcomeOf(index);
		const isLast = index === this.answers.count - 1;

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
		const outOfRange = !Number.isInteger(index) || index < 0 || index >= this.answers.count;
		if (outOfRange || (index > this.furthest && !this.answers.isAnswered(index))) {
			return;
		}
		this.view = { kind: 'question', index };
		this.render();
	}

	public restart(): void {
		this.answers.clear();
		this.furthest = 0;
		this.lastResult = null;
		this.view = { kind: 'question', index: 0 };
		this.render();
	}

	private showResult(): void {
		if (this.lastResult) {
			this.view = { kind: 'result' };
			this.render();
		}
	}

	private download(): void {
		if (this.lastResult && this.textExport) {
			this.textExport.download(this.lastResult);
			this.toast.show(this.strings.downloadDone);
		}
	}

	private get currentIndex(): number {
		return this.view.kind === 'question' ? this.view.index : -1;
	}

	private render(focus = true): void {
		const current = this.currentIndex;

		for (let index = 0; index < this.answers.count; index++) {
			(this.answers.question(index) as HTMLElement).hidden = index !== current;
		}
		if (current >= 0) {
			this.updateQuestionControls(current, false);
		}
		if (this.summary) {
			this.summary.hidden = this.view.kind !== 'result';
			if (this.view.kind === 'result' && this.lastResult) {
				this.summary.render(this.lastResult, this.furthest);
			}
		}
		this.renderSidebar();

		if (focus) {
			this.moveFocus(current);
		}
	}

	private renderSidebar(): void {
		this.sidebar.render(this.currentIndex, this.furthest, this.lastResult !== null);
	}

	private moveFocus(current: number): void {
		const target =
			current >= 0 ? this.answers.question(current)?.querySelector<HTMLElement>('h2') : this.summary?.focusTarget;
		target?.focus({ preventScroll: true });

		const top = this.root.getBoundingClientRect().top;
		if (top < 0 || top > window.innerHeight * 0.4) {
			this.root.scrollIntoView?.({ behavior: 'smooth', block: 'start' });
		}
	}

	private updateQuestionControls(index: number, changed: boolean): void {
		const question = this.answers.question(index) as HTMLElement;
		const next = question.querySelector<HTMLButtonElement>(hook('next'));
		const note = question.querySelector<HTMLElement>(hook('note'));
		const answered = this.answers.isAnswered(index);

		if (next) {
			next.disabled = !answered;
		}
		if (!note) {
			return;
		}

		let text = this.strings[NOTE_KEY[this.answers.outcomeOf(index).outcome]] as string;
		if (!answered) {
			text = this.answers.isSingleChoice(index) ? this.strings.chooseOne : this.strings.chooseMany;
		} else if (!changed) {
			text = this.strings.savedAnswer;
		}
		note.textContent = text;
	}
}
