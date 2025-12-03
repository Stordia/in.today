<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Country;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SplFileObject;

class ImportSimpleMapsCommand extends Command
{
    protected $signature = 'world:import-cities
                            {--truncate : Truncate countries and cities tables before import}
                            {--country= : Import only cities from a specific country (ISO2 code or name)}';

    protected $description = 'Import countries and cities from worldcities.csv (SimpleMaps format)';

    private const BATCH_SIZE = 1000;

    private const CSV_PATH = 'app/world/worldcities.csv';

    private int $countriesCreated = 0;

    private int $countriesUpdated = 0;

    private int $citiesCreated = 0;

    private int $citiesUpdated = 0;

    private int $citiesSkipped = 0;

    private array $countryCache = [];

    private ?string $filterCountry = null;

    public function handle(): int
    {
        $filePath = storage_path(self::CSV_PATH);

        if (! file_exists($filePath)) {
            $this->error("CSV file not found: {$filePath}");
            $this->newLine();
            $this->info('Please download the SimpleMaps World Cities Database and place it at:');
            $this->info("  {$filePath}");
            $this->newLine();
            $this->info('Expected columns: city, city_ascii, lat, lng, country, iso2, iso3, admin_name, capital, population, id');

            return self::FAILURE;
        }

        $this->filterCountry = $this->option('country');
        if ($this->filterCountry) {
            $this->info("Filtering by country: {$this->filterCountry}");
        }

        $this->info("Reading CSV from: {$filePath}");
        $this->newLine();

        if ($this->option('truncate')) {
            if ($this->filterCountry) {
                $this->warn('Cannot use --truncate with --country filter. Ignoring --truncate.');
            } else {
                $this->truncateTables();
            }
        }

        try {
            $this->importCountries($filePath);
            $this->importCities($filePath);
        } catch (\Throwable $e) {
            $this->error("Import failed: {$e->getMessage()}");
            Log::error('World cities import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }

        $this->printSummary();

        return self::SUCCESS;
    }

    private function truncateTables(): void
    {
        $this->warn('Truncating countries and cities tables...');

        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        City::truncate();
        $this->info('  - Cities table truncated');

        Country::truncate();
        $this->info('  - Countries table truncated');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
    }

    private function importCountries(string $filePath): void
    {
        $this->info('Pass 1: Importing countries...');

        $file = new SplFileObject($filePath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = $file->fgetcsv();
        $columnMap = $this->mapColumns($headers);

        $uniqueCountries = [];

        while (! $file->eof()) {
            $row = $file->fgetcsv();
            if (! $row || count($row) < 5) {
                continue;
            }

            $countryName = $this->getValue($row, $columnMap['country']);
            $iso2 = $this->getValue($row, $columnMap['iso2']);

            if (empty($countryName)) {
                continue;
            }

            // Apply country filter
            if ($this->filterCountry) {
                $filterUpper = strtoupper($this->filterCountry);
                $filterLower = strtolower($this->filterCountry);
                if ($iso2 !== $filterUpper && strtolower($countryName) !== $filterLower) {
                    continue;
                }
            }

            // Use ISO2 as key if available, otherwise country name
            $key = $iso2 ?: strtolower($countryName);
            if (! isset($uniqueCountries[$key])) {
                $uniqueCountries[$key] = [
                    'name' => $countryName,
                    'code' => $iso2,
                ];
            }
        }

        $this->info("  Found ".count($uniqueCountries)." unique countries");

        // Upsert countries
        foreach ($uniqueCountries as $data) {
            $this->upsertCountry($data['name'], $data['code']);
        }

        $this->info("  Countries created: {$this->countriesCreated}");
        $this->info("  Countries updated: {$this->countriesUpdated}");
        $this->newLine();
    }

    private function upsertCountry(string $name, ?string $code): Country
    {
        $cacheKey = $code ?: strtolower($name);

        if (isset($this->countryCache[$cacheKey])) {
            return $this->countryCache[$cacheKey];
        }

        // Try to find by code first
        if ($code) {
            $country = Country::where('code', $code)->first();
            if ($country) {
                // Update name/slug if needed
                $updated = false;
                if ($country->name !== $name) {
                    $country->name = $name;
                    $updated = true;
                }
                if (empty($country->slug)) {
                    $country->slug = Str::slug($name);
                    $updated = true;
                }
                if (! $country->is_active) {
                    $country->is_active = true;
                    $updated = true;
                }
                if ($updated) {
                    $country->save();
                }
                $this->countryCache[$cacheKey] = $country;
                $this->countriesUpdated++;

                return $country;
            }
        }

        // Try to find by name
        $country = Country::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if ($country) {
            $updated = false;
            if ($code && empty($country->code)) {
                $country->code = $code;
                $updated = true;
            }
            if (empty($country->slug)) {
                $country->slug = Str::slug($name);
                $updated = true;
            }
            if (! $country->is_active) {
                $country->is_active = true;
                $updated = true;
            }
            if ($updated) {
                $country->save();
            }
            $this->countryCache[$cacheKey] = $country;
            $this->countriesUpdated++;

            return $country;
        }

        // Create new country
        $country = Country::create([
            'name' => $name,
            'code' => $code,
            'slug' => Str::slug($name),
            'is_active' => true,
        ]);

        $this->countryCache[$cacheKey] = $country;
        $this->countriesCreated++;

        return $country;
    }

    private function importCities(string $filePath): void
    {
        $this->info('Pass 2: Importing cities...');

        $file = new SplFileObject($filePath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = $file->fgetcsv();
        $columnMap = $this->mapColumns($headers);

        $rowNumber = 1;
        $batchCount = 0;

        // Count total rows for progress bar
        $totalRows = 0;
        while (! $file->eof()) {
            $file->fgetcsv();
            $totalRows++;
        }
        $file->rewind();
        $file->fgetcsv(); // Skip header again

        $progressBar = $this->output->createProgressBar($totalRows);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% | Created: %created% | Updated: %updated% | Skipped: %skipped%');
        $progressBar->setMessage('0', 'created');
        $progressBar->setMessage('0', 'updated');
        $progressBar->setMessage('0', 'skipped');
        $progressBar->start();

        while (! $file->eof()) {
            $row = $file->fgetcsv();
            $rowNumber++;

            if (! $row || count($row) < 5) {
                continue;
            }

            try {
                $this->processCity($row, $columnMap, $rowNumber);
            } catch (\Throwable $e) {
                Log::debug('City import row error', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                ]);
                $this->citiesSkipped++;
            }

            $batchCount++;
            if ($batchCount % self::BATCH_SIZE === 0) {
                $progressBar->setProgress($batchCount);
                $progressBar->setMessage((string) $this->citiesCreated, 'created');
                $progressBar->setMessage((string) $this->citiesUpdated, 'updated');
                $progressBar->setMessage((string) $this->citiesSkipped, 'skipped');
            }
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    private function processCity(array $row, array $columnMap, int $rowNumber): void
    {
        $countryName = $this->getValue($row, $columnMap['country']);
        $iso2 = $this->getValue($row, $columnMap['iso2']);
        $cityName = $this->getValue($row, $columnMap['city_ascii']) ?: $this->getValue($row, $columnMap['city']);
        $adminName = $this->getValue($row, $columnMap['admin_name']);
        $lat = $this->getNumericValue($row, $columnMap['lat']);
        $lng = $this->getNumericValue($row, $columnMap['lng']);
        $population = $this->getIntValue($row, $columnMap['population']);

        // Skip invalid rows
        if (empty($countryName) || empty($cityName)) {
            $this->citiesSkipped++;

            return;
        }

        // Apply country filter
        if ($this->filterCountry) {
            $filterUpper = strtoupper($this->filterCountry);
            $filterLower = strtolower($this->filterCountry);
            if ($iso2 !== $filterUpper && strtolower($countryName) !== $filterLower) {
                $this->citiesSkipped++;

                return;
            }
        }

        // Find country (should exist from pass 1)
        $cacheKey = $iso2 ?: strtolower($countryName);
        $country = $this->countryCache[$cacheKey] ?? null;

        if (! $country) {
            // Try to look up in DB
            if ($iso2) {
                $country = Country::where('code', $iso2)->first();
            }
            if (! $country) {
                $country = Country::whereRaw('LOWER(name) = ?', [strtolower($countryName)])->first();
            }
            if ($country) {
                $this->countryCache[$cacheKey] = $country;
            }
        }

        if (! $country) {
            $this->citiesSkipped++;

            return;
        }

        // Generate slug
        $baseSlug = Str::slug($cityName);
        $suffix = $country->code ? '-'.strtolower($country->code) : '-'.$country->id;

        // Upsert city using country_id + name + admin_name as composite key
        $city = City::where('country_id', $country->id)
            ->where('name', $cityName)
            ->where(function ($query) use ($adminName) {
                if ($adminName) {
                    $query->where('admin_name', $adminName);
                } else {
                    $query->whereNull('admin_name');
                }
            })
            ->first();

        if ($city) {
            // Update existing city
            $updated = false;
            if ($lat !== null && $city->latitude === null) {
                $city->latitude = $lat;
                $updated = true;
            }
            if ($lng !== null && $city->longitude === null) {
                $city->longitude = $lng;
                $updated = true;
            }
            if ($population !== null && $city->population === null) {
                $city->population = $population;
                $updated = true;
            }
            if ($adminName && $city->admin_name === null) {
                $city->admin_name = $adminName;
                $updated = true;
            }
            if ($updated) {
                $city->save();
                $this->citiesUpdated++;
            } else {
                $this->citiesSkipped++;
            }
        } else {
            // Check for slug uniqueness
            $slug = $baseSlug.$suffix;
            $existingSlugCount = City::where('slug', 'like', $slug.'%')->count();
            if ($existingSlugCount > 0) {
                $slug = $slug.'-'.($existingSlugCount + 1);
            }

            City::create([
                'name' => $cityName,
                'slug' => $slug,
                'country_id' => $country->id,
                'admin_name' => $adminName,
                'country' => $country->code ?: substr($countryName, 0, 2),
                'latitude' => $lat,
                'longitude' => $lng,
                'population' => $population,
                'is_active' => true,
            ]);
            $this->citiesCreated++;
        }
    }

    private function mapColumns(array $headers): array
    {
        $map = [
            'city' => null,
            'city_ascii' => null,
            'lat' => null,
            'lng' => null,
            'country' => null,
            'iso2' => null,
            'iso3' => null,
            'admin_name' => null,
            'capital' => null,
            'population' => null,
            'id' => null,
        ];

        foreach ($headers as $index => $header) {
            $normalized = strtolower(trim((string) $header));
            if (array_key_exists($normalized, $map)) {
                $map[$normalized] = $index;
            }
        }

        return $map;
    }

    private function getValue(array $row, ?int $index): ?string
    {
        if ($index === null || ! isset($row[$index])) {
            return null;
        }

        $value = trim((string) $row[$index]);

        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'ISO-8859-1');
        }

        return $value !== '' ? $value : null;
    }

    private function getNumericValue(array $row, ?int $index): ?float
    {
        $value = $this->getValue($row, $index);
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/[^0-9.\-]/', '', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function getIntValue(array $row, ?int $index): ?int
    {
        $value = $this->getValue($row, $index);
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/[^0-9]/', '', $value);

        return $value !== '' ? (int) $value : null;
    }

    private function printSummary(): void
    {
        $this->info('Import completed!');
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Countries created', $this->countriesCreated],
                ['Countries updated', $this->countriesUpdated],
                ['Cities created', $this->citiesCreated],
                ['Cities updated', $this->citiesUpdated],
                ['Cities skipped', $this->citiesSkipped],
                ['Total countries in DB', Country::count()],
                ['Total cities in DB', City::count()],
            ]
        );
    }
}
