<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\City;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillCityCanonicalSlugs extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'cities:backfill-canonical-slugs
                            {--force : Force backfill even for rows with existing canonical slugs}';

    /**
     * The console command description.
     */
    protected $description = 'Backfill slug_canonical column for all cities based on Str::slug(name)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = $this->option('force');

        $this->info('Starting canonical slug backfill for cities...');

        $query = City::query();

        if (! $force) {
            // Only update cities without canonical slug (idempotent)
            $query->whereNull('slug_canonical')
                ->orWhere('slug_canonical', '');
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('No cities need backfilling.');

            return self::SUCCESS;
        }

        $this->info("Processing {$total} cities...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $updated = 0;

        $query->chunkById(1000, function ($cities) use (&$updated, $bar) {
            foreach ($cities as $city) {
                $canonicalSlug = Str::slug($city->name);

                if ($canonicalSlug !== '' && $city->slug_canonical !== $canonicalSlug) {
                    $city->slug_canonical = $canonicalSlug;
                    $city->save();
                    $updated++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->info("✓ Backfill complete. Updated {$updated} cities.");

        return self::SUCCESS;
    }
}
