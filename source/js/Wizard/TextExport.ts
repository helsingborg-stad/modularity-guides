import type { Answers } from './Answers';
import { hook, hookAttribute } from './hooks';
import { LABEL_KEY, type Result } from './outcome';
import { formatDate, type WizardStrings } from './strings';

const clean = (text: string | null | undefined): string => (text ?? '').replace(/\s+/g, ' ').trim();

/**
 * Builds the plain text summary that visitors can save.
 */
export class TextExport {
	constructor(
		private readonly root: HTMLElement,
		private readonly summary: HTMLElement,
		private readonly answers: Answers,
		private readonly strings: WizardStrings,
	) {}

	get title(): string {
		return clean(this.root.getAttribute(hookAttribute('title')));
	}

	text(result: Result): string {
		const hero = this.summary.querySelector<HTMLElement>(`[${hookAttribute('hero')}="${result.type}"]`);
		const lines: string[] = [
			this.title.toUpperCase(),
			`${this.strings.created} ${formatDate()}`,
			'',
			clean(hero?.querySelector('.c-notice__title')?.textContent),
		];
		if (result.reason) {
			lines.push(result.reason);
		}
		lines.push(clean(hero?.querySelector(hook('hero-text'))?.textContent), '', this.strings.yourAnswers.toUpperCase());

		for (let index = 0; index < this.answers.count; index++) {
			lines.push(...this.answerLines(index), '');
		}
		lines.push(...this.contentLines());

		const disclaimer = clean(this.summary.querySelector(`${hook('disclaimer')} [data-js-notice-message]`)?.textContent);
		if (disclaimer) {
			lines.push(disclaimer, '');
		}
		lines.push(window.location.href);

		return lines.join('\r\n');
	}

	/** File name derived from the guide title, e.g. "kom-igang-med-ekonomicoachen.txt". */
	fileName(): string {
		const slug = (this.title || 'guide')
			.toLowerCase()
			.normalize('NFD')
			.replace(/[̀-ͯ]/g, '')
			.replace(/[^a-z0-9]+/g, '-')
			.replace(/^-|-$/g, '');
		return `${slug || 'guide'}.txt`;
	}

	download(result: Result): void {
		const blob = new Blob([`﻿${this.text(result)}`], { type: 'text/plain;charset=utf-8' });
		const url = URL.createObjectURL(blob);
		const link = document.createElement('a');
		link.href = url;
		link.download = this.fileName();
		document.body.append(link);
		link.click();
		link.remove();
		window.setTimeout(() => URL.revokeObjectURL(url), 30000);
	}

	private answerLines(index: number): string[] {
		const labels = this.answers.labels(index);
		const lines = [`${index + 1}. ${this.answers.shortTitle(index)}`];

		if (labels.length === 0) {
			return [...lines, `   ${this.strings.notAnswered}`];
		}
		lines.push(...labels.map((label) => `   - ${label}`));

		const { outcome } = this.answers.outcomeOf(index);
		if (outcome !== 'pass') {
			lines.push(`   (${this.strings[LABEL_KEY[outcome]]})`);
		}
		return lines;
	}

	/** Visible summary content, e.g. a todo list filtered by the answers. */
	private contentLines(): string[] {
		const lines: string[] = [];

		this.summary.querySelectorAll<HTMLElement>(`[${hookAttribute('panel')}="content"]`).forEach((panel) => {
			const items = [...panel.querySelectorAll<HTMLElement>('h4, label, li, p')]
				.filter((element) => !element.closest('.c-modal, dialog, form') && element.checkVisibility?.() !== false)
				.map((element) => ({ tag: element.tagName, text: clean(element.textContent) }))
				.filter((item) => item.text !== '')
				.map((item) => (item.tag === 'H4' ? `\n${item.text}` : `   - ${item.text}`));

			if (items.length > 0) {
				lines.push(clean(panel.querySelector('h3')?.textContent).toUpperCase(), ...items, '');
			}
		});

		return lines;
	}
}
