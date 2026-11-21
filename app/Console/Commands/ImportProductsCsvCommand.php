<?php

namespace App\Console\Commands;

use App\Services\ProductBulkImportService;
use Illuminate\Console\Command;

class ImportProductsCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:import-csv
                            {file? : Path to the CSV file relative to base path or absolute}
                            {--markup=250 : Markup amount to add to price in PKR}
                            {--stock=50 : Default stock quantity}
                            {--category= : Fallback category ID}
                            {--no-update : Skip updating existing products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk import products from CSV file with Shopify CDN image URLs and price markup';

    /**
     * Execute the console command.
     */
    public function handle(ProductBulkImportService $importer): int
    {
        $filePath = $this->argument('file') ?: database_path('data/products.csv');

        if (!file_exists($filePath)) {
            $this->error("CSV file not found at: {$filePath}");
            return Command::FAILURE;
        }

        $markup         = (float) $this->option('markup');
        $defaultStock   = (int) $this->option('stock');
        $categoryId     = $this->option('category') ? (int) $this->option('category') : null;
        $updateExisting = !$this->option('no-update');

        $this->info("Starting bulk import from: {$filePath}");
        $this->info("Configuration: Markup=+Rs. {$markup}, Default Stock={$defaultStock}");

        $start = microtime(true);

        try {
            $results = $importer->importFromCsv($filePath, [
                'markup'              => $markup,
                'default_stock'       => $defaultStock,
                'default_category_id' => $categoryId,
                'update_existing'     => $updateExisting,
            ]);

            $duration = round(microtime(true) - $start, 2);

            $this->newLine();
            $this->info("=== Import Completed in {$duration}s ===");
            $this->table(
                ['Total Rows Processed', 'New Products Created', 'Products Updated', 'Skipped Rows'],
                [[$results['total'], $results['imported'], $results['updated'], $results['skipped']]]
            );

            if (!empty($results['errors'])) {
                $this->warn("Encountered " . count($results['errors']) . " row errors:");
                foreach (array_slice($results['errors'], 0, 10) as $err) {
                    $this->line(" - {$err}");
                }
                if (count($results['errors']) > 10) {
                    $this->line(" ... and " . (count($results['errors']) - 10) . " more (check storage/logs/laravel.log).");
                }
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Bulk import failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
