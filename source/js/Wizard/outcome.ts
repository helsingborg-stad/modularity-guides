import type { WizardStrings } from './strings';

export type Outcome = 'pass' | 'stop' | 'review';

export interface Result {
	type: Outcome;
	reason: string;
	/** The question that ended the guide, or null when every question was passed. */
	questionIndex: number | null;
}

export const OUTCOMES: Outcome[] = ['pass', 'stop', 'review'];

const WEIGHT: Record<Outcome, number> = { pass: 0, review: 1, stop: 2 };

export const NOTE_KEY: Record<Outcome, keyof WizardStrings> = {
	pass: 'notePass',
	stop: 'noteStop',
	review: 'noteReview',
};

export const LABEL_KEY: Record<Exclude<Outcome, 'pass'>, keyof WizardStrings> = {
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
			return WEIGHT[outcome] > WEIGHT[worst.outcome]
				? { outcome, reason: input.dataset.jsGuideWizardReason ?? '' }
				: worst;
		},
		{ outcome: 'pass' as Outcome, reason: '' },
	);
}
