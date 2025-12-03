<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds a minimal set of demo countries and cities for development environments.
 *
 * This seeder provides fallback geo data when the full SimpleMaps worldcities.csv
 * is not available. It is safe to run multiple times (uses updateOrCreate).
 *
 * For full geo data, use: php artisan world:import-cities
 */
class WorldDirectoryDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding demo countries and cities...');

        $countries = [
            [
                'name' => 'Germany',
                'code' => 'DE',
                'cities' => [
                    ['name' => 'Berlin', 'admin_name' => 'Berlin', 'lat' => 52.520008, 'lng' => 13.404954, 'population' => 3644826],
                    ['name' => 'Hamburg', 'admin_name' => 'Hamburg', 'lat' => 53.551086, 'lng' => 9.993682, 'population' => 1841179],
                    ['name' => 'Munich', 'admin_name' => 'Bavaria', 'lat' => 48.135125, 'lng' => 11.581981, 'population' => 1471508],
                    ['name' => 'Cologne', 'admin_name' => 'North Rhine-Westphalia', 'lat' => 50.937531, 'lng' => 6.960279, 'population' => 1085664],
                    ['name' => 'Frankfurt', 'admin_name' => 'Hesse', 'lat' => 50.110922, 'lng' => 8.682127, 'population' => 753056],
                ],
            ],
            [
                'name' => 'Greece',
                'code' => 'GR',
                'cities' => [
                    ['name' => 'Athens', 'admin_name' => 'Attica', 'lat' => 37.983810, 'lng' => 23.727539, 'population' => 3153255],
                    ['name' => 'Thessaloniki', 'admin_name' => 'Central Macedonia', 'lat' => 40.640063, 'lng' => 22.944419, 'population' => 1030338],
                    ['name' => 'Patras', 'admin_name' => 'Western Greece', 'lat' => 38.246639, 'lng' => 21.734573, 'population' => 213984],
                    ['name' => 'Heraklion', 'admin_name' => 'Crete', 'lat' => 35.338735, 'lng' => 25.144213, 'population' => 173993],
                ],
            ],
            [
                'name' => 'United States',
                'code' => 'US',
                'cities' => [
                    ['name' => 'New York', 'admin_name' => 'New York', 'lat' => 40.712776, 'lng' => -74.005974, 'population' => 8336817],
                    ['name' => 'Los Angeles', 'admin_name' => 'California', 'lat' => 34.052234, 'lng' => -118.243685, 'population' => 3979576],
                    ['name' => 'Chicago', 'admin_name' => 'Illinois', 'lat' => 41.878114, 'lng' => -87.629798, 'population' => 2693976],
                    ['name' => 'Houston', 'admin_name' => 'Texas', 'lat' => 29.760427, 'lng' => -95.369803, 'population' => 2320268],
                    ['name' => 'Miami', 'admin_name' => 'Florida', 'lat' => 25.761680, 'lng' => -80.191790, 'population' => 467963],
                ],
            ],
            [
                'name' => 'Italy',
                'code' => 'IT',
                'cities' => [
                    ['name' => 'Rome', 'admin_name' => 'Lazio', 'lat' => 41.902783, 'lng' => 12.496366, 'population' => 2872800],
                    ['name' => 'Milan', 'admin_name' => 'Lombardy', 'lat' => 45.464204, 'lng' => 9.189982, 'population' => 1378689],
                    ['name' => 'Naples', 'admin_name' => 'Campania', 'lat' => 40.851799, 'lng' => 14.268120, 'population' => 959574],
                    ['name' => 'Turin', 'admin_name' => 'Piedmont', 'lat' => 45.070312, 'lng' => 7.686856, 'population' => 870456],
                    ['name' => 'Florence', 'admin_name' => 'Tuscany', 'lat' => 43.769560, 'lng' => 11.255814, 'population' => 382258],
                ],
            ],
            [
                'name' => 'France',
                'code' => 'FR',
                'cities' => [
                    ['name' => 'Paris', 'admin_name' => 'Île-de-France', 'lat' => 48.856614, 'lng' => 2.352222, 'population' => 2161000],
                    ['name' => 'Marseille', 'admin_name' => 'Provence-Alpes-Côte d\'Azur', 'lat' => 43.296482, 'lng' => 5.369780, 'population' => 861635],
                    ['name' => 'Lyon', 'admin_name' => 'Auvergne-Rhône-Alpes', 'lat' => 45.764043, 'lng' => 4.835659, 'population' => 513275],
                    ['name' => 'Nice', 'admin_name' => 'Provence-Alpes-Côte d\'Azur', 'lat' => 43.710173, 'lng' => 7.261953, 'population' => 341522],
                ],
            ],
            [
                'name' => 'Spain',
                'code' => 'ES',
                'cities' => [
                    ['name' => 'Madrid', 'admin_name' => 'Madrid', 'lat' => 40.416775, 'lng' => -3.703790, 'population' => 3223334],
                    ['name' => 'Barcelona', 'admin_name' => 'Catalonia', 'lat' => 41.385064, 'lng' => 2.173404, 'population' => 1620343],
                    ['name' => 'Valencia', 'admin_name' => 'Valencia', 'lat' => 39.469907, 'lng' => -0.376288, 'population' => 791413],
                    ['name' => 'Seville', 'admin_name' => 'Andalusia', 'lat' => 37.389092, 'lng' => -5.984459, 'population' => 688711],
                ],
            ],
            [
                'name' => 'United Kingdom',
                'code' => 'GB',
                'cities' => [
                    ['name' => 'London', 'admin_name' => 'England', 'lat' => 51.507351, 'lng' => -0.127758, 'population' => 8982000],
                    ['name' => 'Birmingham', 'admin_name' => 'England', 'lat' => 52.486243, 'lng' => -1.890401, 'population' => 1141816],
                    ['name' => 'Manchester', 'admin_name' => 'England', 'lat' => 53.480759, 'lng' => -2.242631, 'population' => 547627],
                    ['name' => 'Edinburgh', 'admin_name' => 'Scotland', 'lat' => 55.953252, 'lng' => -3.188267, 'population' => 488050],
                ],
            ],
            [
                'name' => 'Netherlands',
                'code' => 'NL',
                'cities' => [
                    ['name' => 'Amsterdam', 'admin_name' => 'North Holland', 'lat' => 52.370216, 'lng' => 4.895168, 'population' => 872680],
                    ['name' => 'Rotterdam', 'admin_name' => 'South Holland', 'lat' => 51.924420, 'lng' => 4.477733, 'population' => 651446],
                    ['name' => 'The Hague', 'admin_name' => 'South Holland', 'lat' => 52.078663, 'lng' => 4.288788, 'population' => 545838],
                ],
            ],
        ];

        $countriesCreated = 0;
        $countriesUpdated = 0;
        $citiesCreated = 0;
        $citiesUpdated = 0;

        foreach ($countries as $countryData) {
            // Upsert country
            $country = Country::where('code', $countryData['code'])->first();

            if ($country) {
                $country->update([
                    'name' => $countryData['name'],
                    'slug' => Str::slug($countryData['name']),
                    'is_active' => true,
                ]);
                $countriesUpdated++;
            } else {
                $country = Country::create([
                    'name' => $countryData['name'],
                    'code' => $countryData['code'],
                    'slug' => Str::slug($countryData['name']),
                    'is_active' => true,
                ]);
                $countriesCreated++;
            }

            // Upsert cities
            foreach ($countryData['cities'] as $cityData) {
                $city = City::where('country_id', $country->id)
                    ->where('name', $cityData['name'])
                    ->first();

                $slug = Str::slug($cityData['name']).'-'.strtolower($country->code);

                if ($city) {
                    $city->update([
                        'slug' => $slug,
                        'admin_name' => $cityData['admin_name'],
                        'latitude' => $cityData['lat'],
                        'longitude' => $cityData['lng'],
                        'population' => $cityData['population'],
                        'country' => $country->code,
                        'is_active' => true,
                    ]);
                    $citiesUpdated++;
                } else {
                    City::create([
                        'name' => $cityData['name'],
                        'slug' => $slug,
                        'country_id' => $country->id,
                        'admin_name' => $cityData['admin_name'],
                        'country' => $country->code,
                        'latitude' => $cityData['lat'],
                        'longitude' => $cityData['lng'],
                        'population' => $cityData['population'],
                        'is_active' => true,
                    ]);
                    $citiesCreated++;
                }
            }
        }

        $this->command->info("Countries: {$countriesCreated} created, {$countriesUpdated} updated");
        $this->command->info("Cities: {$citiesCreated} created, {$citiesUpdated} updated");
        $this->command->info('Demo geo data seeded successfully!');
    }
}
