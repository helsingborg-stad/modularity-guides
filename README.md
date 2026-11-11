# Modularity Guides

Addon for Modularity for creating guides

## How a guide works

Guides are shown **step by step**: one question at a time, with a progress sidebar and a summary page.

- **Questions:** every step that contains a *Checkboxes* layout becomes a question.
  Text, modal text and related guides in the same step are shown under the answers (they still
  react to toggle keys).
- **Summary content:** steps without answer options (for example the *Todo list*) are shown on the
  summary page, filtered by the visitor's answers.
- **Guides without questions:** if no step has answer options, the content is shown as it is,
  without the step by step frame.
- **Per step:** *Short title* (sidebar and summary), *Icon* (Material Symbols name), *Description*,
  *Help text* and *Answers*: one answer (radio buttons) or several answers (checkboxes, default).
- **Per answer:** *Description* and *Outcome*:
  - *Continue to next question* (default): the visitor continues.
  - *Stop the guide*: the guide ends and the summary shows the *Reason*.
  - *Needs to be checked*: as stop, but presented as something to look into, e.g. "I don't know".

  With several answers, the most severe outcome wins (stop before check before continue).
  Outcome tags are only shown on questions where an answer can end the guide.
- **Summary page:** titles and texts for each outcome and a notice. Empty fields use default texts.
  Visitors can edit answers, print/save as PDF, save their answers as a text file or start over.
  Nothing is sent or stored.

## Migrating from the accordion mode

Earlier versions showed guides as an accordion. Existing guides work in the step by step mode
without changes, but run the migration once per site after deploying:

```bash
wp modularity-guides migrate-to-wizard --dry-run --url=https://example.com
wp modularity-guides migrate-to-wizard --url=https://example.com
```

It sets *several answers* explicitly on every question, removes accordion-only data
(`display_mode`, the unused *Required* flag on checkboxes) and lists what editors should review:
guides without questions, steps without answers in the middle of a guide (they move to the
summary page) and checkboxes that used to be required.

## Development

```bash
composer install
npm ci
npm run build
```

### Tests and linting

```bash
vendor/bin/phpunit
npx jest
vendor/bin/mago lint source/php
npx biome lint ./source
```

### Translations

Run `languages/localize.sh` after changing strings (make-pot, update-po, make-mo).
Texts with numbers in the step by step script are prepared server side with `_n_noop()`,
so add plural forms in the `.po` file rather than in TypeScript.

### Styling

`source/sass/_wizard.scss` lives in the theme's `plugin` cascade layer and is built on the design
system tokens (`--space`, `--border-radius`, `--font-size-*`, `--color--*` …). Every token can be
overridden for the guide only with `--mod-guide-wizard--<token>`, e.g.
`--mod-guide-wizard--space: 0.75`. The layout uses container queries on the module, so it adapts
to the width of the page area it is placed in.

### Script

`source/js/Wizard/` holds the step by step script: `GuideWizard` (navigation), `Answers` (reads the
inputs), `Sidebar`, `Summary`, `TextExport`, `Toast` and small helpers.
