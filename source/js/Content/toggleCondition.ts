/**
 * Whether one toggle key condition matches the checked toggle keys.
 *
 * A condition is a toggle key ("a"), an AND expression ("a+b") or an AND NOT expression ("a-b").
 * A condition that is exactly one of the guide's toggle keys is matched as that key, so keys with
 * a hyphen (e.g. the generated "answer-1a2b3c4d") are not read as "answer AND NOT 1a2b3c4d".
 *
 * @param condition One item of a comma separated (OR) list
 * @param checked   Toggle keys of the checked answers
 * @param allKeys   Toggle keys of all answers in the guide
 */
export function matchesToggleCondition(condition: string, checked: string[], allKeys: Set<string>): boolean {
	if (allKeys.has(condition)) {
		return checked.includes(condition);
	}

	const and = condition.match(/(^|\+)([^+-]+)/g);
	const andPattern = new RegExp(`\\b(${and?.join('|').replace('+', '')})\\b`, 'ig');
	const andMatches = checked.join(',').match(andPattern);
	const andIsMatching = andMatches !== null && andMatches.length === and?.length;

	const andnot = condition.match(/-([^+-]+)/g);
	let andnotIsMatching = true;
	if (andnot !== null) {
		const andnotPattern = new RegExp(`\\b(${andnot.join('|').replace('-', '')})\\b`, 'ig');
		const andnotMatches = checked.join(',').match(andnotPattern);
		andnotIsMatching = !(andnotMatches !== null && andnotMatches.length > 0);
	}

	return andIsMatching && andnotIsMatching;
}
