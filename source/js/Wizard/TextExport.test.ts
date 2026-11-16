import { Answers } from './Answers';
import type { WizardStrings } from './strings';
import { TextExport } from './TextExport';

const strings = { created: 'Created', yourAnswers: 'Your answers', notAnswered: 'Not answered' } as WizardStrings;

function exporter(title: string): TextExport {
	document.body.innerHTML = `<div data-js-guide-wizard-title="${title}"><section></section></div>`;
	const root = document.querySelector<HTMLElement>('div') as HTMLElement;
	return new TextExport(root, root.querySelector('section') as HTMLElement, new Answers([]), strings);
}

describe('TextExport', () => {
	it('derives a file name from the guide title', () => {
		expect(exporter('Kom igång med Ekonomicoachen').fileName()).toBe('kom-igang-med-ekonomicoachen.txt');
		expect(exporter('').fileName()).toBe('guide.txt');
		expect(exporter('!!!').fileName()).toBe('guide.txt');
	});
});
