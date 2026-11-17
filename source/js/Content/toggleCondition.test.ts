import { matchesToggleCondition } from './toggleCondition';

describe('matchesToggleCondition', () => {
	const allKeys = new Set(['answer-1a2b', 'answer-9z8y', 'a', 'b']);

	it('matches a generated key with a hyphen as that key', () => {
		expect(matchesToggleCondition('answer-1a2b', ['answer-1a2b'], allKeys)).toBe(true);
		expect(matchesToggleCondition('answer-1a2b', ['answer-9z8y'], allKeys)).toBe(false);
		expect(matchesToggleCondition('answer-1a2b', [], allKeys)).toBe(false);
	});

	it('keeps AND and AND NOT expressions', () => {
		expect(matchesToggleCondition('a+b', ['a', 'b'], allKeys)).toBe(true);
		expect(matchesToggleCondition('a+b', ['a'], allKeys)).toBe(false);
		expect(matchesToggleCondition('a-b', ['a'], allKeys)).toBe(true);
		expect(matchesToggleCondition('a-b', ['a', 'b'], allKeys)).toBe(false);
	});
});
