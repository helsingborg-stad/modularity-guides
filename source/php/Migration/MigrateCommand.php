<?php

declare(strict_types=1);

namespace ModularityGuides\Migration;

/**
 * WP-CLI: wp modularity-guides migrate-to-wizard [--dry-run] [--url=<site>]
 *
 * Run once per site after deploying the step by step mode. Start with --dry-run.
 */
class MigrateCommand
{
    public function __construct(
        private WizardMigration $migration,
    ) {}

    /**
     * Migrates guides from the accordion mode to the step by step mode.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Show what would change without saving anything.
     *
     * ## EXAMPLES
     *
     *     wp modularity-guides migrate-to-wizard --dry-run --url=https://example.com
     *
     * @subcommand migrate-to-wizard
     */
    public function migrateToWizard(array $args, array $assocArgs): void
    {
        $plans = $this->migration->plan();
        $warnings = 0;

        foreach ($plans as $report) {
            \WP_CLI::log(sprintf('#%d %s', $report['id'], $report['title'] !== '' ? $report['title'] : '(no title)'));
            foreach ($report['changes'] as $change) {
                \WP_CLI::log('  - ' . $change['description']);
            }
            foreach ($report['warnings'] as $warning) {
                \WP_CLI::warning('  ' . $warning);
                $warnings++;
            }
        }

        $summary = sprintf('%d guide(s), %d warning(s) for editors to review.', count($plans), $warnings);
        if (\WP_CLI\Utils\get_flag_value($assocArgs, 'dry-run', false)) {
            \WP_CLI::success('Dry run, nothing saved. ' . $summary);
            return;
        }

        $this->migration->apply($plans);
        \WP_CLI::success('Migrated. ' . $summary);
    }
}
