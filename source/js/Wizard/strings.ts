/**
 * Texts prepared by the view model (Helper\Wizard\ScriptStrings).
 * Texts containing numbers are prepared for every possible value, so plural forms
 * are handled by WordPress and the script never formats strings.
 */
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
	/** Neutral note used when the outcomes are hidden on the answers. */
	noteChosen: string;
	/** Setting "Show outcomes on answers" turned off. */
	hideOutcomes: boolean;
}

export function parseStrings(json: string | null): WizardStrings {
	return JSON.parse(json || '{}') as WizardStrings;
}

export function setText(element: Element | null, text: string | undefined): void {
	if (element) {
		element.textContent = text ?? '';
	}
}

export function formatDate(date = new Date()): string {
	try {
		return new Intl.DateTimeFormat(document.documentElement.lang || undefined, {
			dateStyle: 'long',
			timeStyle: 'short',
		}).format(date);
	} catch {
		return date.toLocaleString();
	}
}
