import { GuideWizard } from './GuideWizard';
import { ROOT } from './hooks';

export function initWizards(): GuideWizard[] {
	return [...document.querySelectorAll<HTMLElement>(`[${ROOT}]`)].map((root) => new GuideWizard(root));
}

window.addEventListener('DOMContentLoaded', () => {
	initWizards();
});
