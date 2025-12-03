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

class ImportWorldCitiesCommand extends Command
{
    protected $signature = 'geo:import-world-cities
                            {--truncate : Truncate countries and cities tables before import}
                            {--file= : Custom CSV file path (default: storage/app/geo/world_cities.csv)}';

    protected $description = 'Import world countries and cities from a CSV file';

    private const BATCH_SIZE = 500;

    private int $countriesCreated = 0;

    private int $countriesUpdated = 0;

    private int $citiesCreated = 0;

    private int $citiesSkipped = 0;

    private array $countryCache = [];

    public function handle(): int
    {
        $filePath = $this->option('file') ?: storage_path('app/geo/world_cities.csv');

        if (! file_exists($filePath)) {
            $this->error("CSV file not found: {$filePath}");
            $this->newLine();
            $this->info('Please upload a CSV file with the following columns:');
            $this->info('  - country_name (required)');
            $this->info('  - country_code (optional, e.g. "DE")');
            $this->info('  - city_name (required)');
            $this->info('  - state_name (optional, ignored)');
            $this->info('  - latitude (optional)');
            $this->info('  - longitude (optional)');

            return self::FAILURE;
        }

        $this->info("Reading CSV from: {$filePath}");
        $this->newLine();

        if ($this->option('truncate')) {
            $this->truncateTables();
        }

        try {
            $this->importCsv($filePath);
        } catch (\Throwable $e) {
            $this->error("Import failed: {$e->getMessage()}");
            Log::error('Geo import failed', [
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

        // Truncate cities first (has FK to countries)
        City::truncate();
        $this->info('  - Cities table truncated');

        // Truncate countries
        Country::truncate();
        $this->info('  - Countries table truncated');

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
    }

    private function importCsv(string $filePath): void
    {
        $file = new SplFileObject($filePath, 'r');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        // Read header row
        $headers = $file->fgetcsv();
        if (! $headers) {
            throw new \RuntimeException('CSV file is empty or has no header row');
        }

        $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $headers);
        $columnMap = $this->mapColumns($headers);

        if ($columnMap['country_name'] === null) {
            throw new \RuntimeException('CSV must have a "country_name" column');
        }
        if ($columnMap['city_name'] === null) {
            throw new \RuntimeException('CSV must have a "city_name" column');
        }

        $this->info('Detected columns: '.implode(', ', array_keys(array_filter($columnMap, fn ($v) => $v !== null))));
        $this->newLine();

        $rowNumber = 1; // Header is row 1
        $batchCount = 0;
        $progressBar = $this->output->createProgressBar();
        $progressBar->setFormat(' %current% rows [%bar%] %elapsed:6s% | Countries: %countries% | Cities: %cities%');
        $progressBar->setMessage('0', 'countries');
        $progressBar->setMessage('0', 'cities');
        $progressBar->start();

        while (! $file->eof()) {
            $row = $file->fgetcsv();
            $rowNumber++;

            if (! $row || count($row) < 2) {
                continue;
            }

            try {
                $this->processRow($row, $columnMap, $rowNumber);
            } catch (\Throwable $e) {
                Log::warning('Geo import row error', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                    'data' => $row,
                ]);
                $this->citiesSkipped++;
            }

            $batchCount++;
            if ($batchCount % self::BATCH_SIZE === 0) {
                $progressBar->setProgress($batchCount);
                $progressBar->setMessage((string) ($this->countriesCreated + $this->countriesUpdated), 'countries');
                $progressBar->setMessage((string) $this->citiesCreated, 'cities');
            }
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    private function mapColumns(array $headers): array
    {
        $map = [
            'country_name' => null,
            'country_code' => null,
            'city_name' => null,
            'state_name' => null,
            'latitude' => null,
            'longitude' => null,
        ];

        foreach ($headers as $index => $header) {
            // Normalize header variations
            $normalized = str_replace(['-', ' '], '_', $header);

            if (in_array($normalized, ['country_name', 'country', 'countryname'])) {
                $map['country_name'] = $index;
            } elseif (in_array($normalized, ['country_code', 'countrycode', 'iso2', 'iso_code', 'code'])) {
                $map['country_code'] = $index;
            } elseif (in_array($normalized, ['city_name', 'city', 'cityname', 'name'])) {
                $map['city_name'] = $index;
            } elseif (in_array($normalized, ['state_name', 'state', 'statename', 'region', 'admin_name'])) {
                $map['state_name'] = $index;
            } elseif (in_array($normalized, ['latitude', 'lat'])) {
                $map['latitude'] = $index;
            } elseif (in_array($normalized, ['longitude', 'lng', 'lon', 'long'])) {
                $map['longitude'] = $index;
            }
        }

        return $map;
    }

    private function processRow(array $row, array $columnMap, int $rowNumber): void
    {
        $countryName = $this->getValue($row, $columnMap['country_name']);
        $countryCode = $this->getValue($row, $columnMap['country_code']);
        $cityName = $this->getValue($row, $columnMap['city_name']);
        $latitude = $this->getNumericValue($row, $columnMap['latitude']);
        $longitude = $this->getNumericValue($row, $columnMap['longitude']);

        // Skip invalid rows
        if (empty($countryName) || empty($cityName)) {
            $this->citiesSkipped++;

            return;
        }

        // Normalize country code to uppercase
        if ($countryCode) {
            $countryCode = strtoupper($countryCode);
        }

        // Find or create country
        $country = $this->findOrCreateCountry($countryName, $countryCode);

        // Generate unique slug for city (city-countrycode format)
        $citySlug = $this->generateCitySlug($cityName, $country);

        // Find or create city
        $city = City::where('country_id', $country->id)
            ->where('name', $cityName)
            ->first();

        if ($city) {
            // Update coordinates if they were empty and now we have them
            $updated = false;
            if ($latitude !== null && $city->latitude === null) {
                $city->latitude = $latitude;
                $updated = true;
            }
            if ($longitude !== null && $city->longitude === null) {
                $city->longitude = $longitude;
                $updated = true;
            }
            if ($updated) {
                $city->save();
            }
            $this->citiesSkipped++;
        } else {
            City::create([
                'name' => $cityName,
                'slug' => $citySlug,
                'country_id' => $country->id,
                'country' => $countryCode ?: substr($country->code ?? 'XX', 0, 2),
                'latitude' => $latitude,
                'longitude' => $longitude,
                'is_active' => true,
            ]);
            $this->citiesCreated++;
        }
    }

    private function findOrCreateCountry(string $name, ?string $code): Country
    {
        // Check cache first
        $cacheKey = $code ?: strtolower($name);
        if (isset($this->countryCache[$cacheKey])) {
            return $this->countryCache[$cacheKey];
        }

        // Try to find by code first (most reliable)
        if ($code) {
            $country = Country::where('code', $code)->first();
            if ($country) {
                $this->countryCache[$cacheKey] = $country;
                $this->countriesUpdated++;

                return $country;
            }
        }

        // Try to find by name (case-insensitive)
        $country = Country::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
        if ($country) {
            // Update code if we have one and the country doesn't
            if ($code && empty($country->code)) {
                $country->code = $code;
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

    private function generateCitySlug(string $cityName, Country $country): string
    {
        $baseSlug = Str::slug($cityName);
        $suffix = $country->code ? '-'.strtolower($country->code) : '-'.$country->id;

        $slug = $baseSlug.$suffix;

        // Check for uniqueness and append number if needed
        $existingCount = City::where('slug', 'like', $slug.'%')->count();
        if ($existingCount > 0) {
            $slug = $slug.'-'.($existingCount + 1);
        }

        return $slug;
    }

    private function getValue(array $row, ?int $index): ?string
    {
        if ($index === null || ! isset($row[$index])) {
            return null;
        }

        $value = trim((string) $row[$index]);

        // Handle UTF-8 encoding issues
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

        // Remove any non-numeric characters except . and -
        $value = preg_replace('/[^0-9.\-]/', '', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function printSummary(): void
    {
        $this->info('Import completed!');
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Countries created', $this->countriesCreated],
                ['Countries found/updated', $this->countriesUpdated],
                ['Cities created', $this->citiesCreated],
                ['Cities skipped/updated', $this->citiesSkipped],
                ['Total countries', Country::count()],
                ['Total cities', City::count()],
            ]
        );
    }
}
