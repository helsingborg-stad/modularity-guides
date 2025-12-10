import { createViteConfig } from "vite-config-factory";

const entries = {
    'js/modularity-guides': './source/js/modularity-guides.ts',
    'css/modularity-guides': './source/sass/modularity-guides.scss',
};

export default createViteConfig(entries, {
	outDir: "assets/dist",
	manifestFile: "manifest.json",
});
