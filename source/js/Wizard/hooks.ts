/** Root attribute of a wizard. Its value is the JSON encoded WizardStrings. */
export const ROOT = 'data-js-guide-wizard';

/** Selector for a data-js-guide-wizard-<name> hook. */
export const hook = (name: string): string => `[${ROOT}-${name}]`;

/** Attribute name for a data-js-guide-wizard-<name> hook. */
export const hookAttribute = (name: string): string => `${ROOT}-${name}`;
