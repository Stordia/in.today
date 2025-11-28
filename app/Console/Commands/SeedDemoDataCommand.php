<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\GlobalRole;
use App\Enums\ReservationSource;
use App\Enums\ReservationStatus;
use App\Enums\RestaurantPlan;
use App\Enums\RestaurantRole;
use App\Enums\WaitlistStatus;
use App\Models\Agency;
use App\Models\BlockedDate;
use App\Models\City;
use App\Models\Cuisine;
use App\Models\OpeningHour;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\RestaurantUser;
use App\Models\Table;
use App\Models\User;
use App\Models\Waitlist;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SeedDemoDataCommand extends Command
{
    protected $signature = 'app:seed-demo
                            {--fresh : Truncate demo-related tables before seeding}
                            {--force : Skip production environment check (dangerous!)}';

    protected $description = 'Seed demo data for development and QA testing';

    private array $createdData = [
        'users' => [],
        'agencies' => 0,
        'restaurants' => 0,
        'tables' => 0,
        'opening_hours' => 0,
        'blocked_dates' => 0,
        'reservations' => 0,
        'waitlist' => 0,
    ];

    public function handle(): int
    {
        // Safety check: refuse to run in production
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('This command cannot run in production environment!');
            $this->error('If you really know what you are doing, use --force flag.');

            return self::FAILURE;
        }

        if (app()->environment('production') && $this->option('force')) {
            $this->warn('⚠️  Running in PRODUCTION with --force flag!');
            if (! $this->confirm('Are you absolutely sure you want to seed demo data in PRODUCTION?')) {
                return self::FAILURE;
            }
        }

        $this->info('');
        $this->info('🌱 Starting Demo Data Seeding...');
        $this->info('');

        if ($this->option('fresh')) {
            $this->freshTables();
        }

        DB::beginTransaction();

        try {
            $this->createPlatformAdmin();
            $this->createCitiesAndCuisines();
            $agency = $this->createAgency();
            $restaurants = $this->createRestaurants($agency);
            $this->createRestaurantUsers($restaurants);
            $this->createOperationalData($restaurants);

            DB::commit();

            $this->printSummary();

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to seed demo data: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    private function freshTables(): void
    {
        if (! $this->confirm('This will DELETE all data from demo-related tables. Continue?', false)) {
            $this->info('Aborting...');
            exit(self::FAILURE);
        }

        $this->info('Truncating tables...');

        // Disable foreign key checks for truncation
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Order matters due to foreign keys
        Waitlist::truncate();
        Reservation::truncate();
        BlockedDate::truncate();
        OpeningHour::truncate();
        Table::truncate();
        RestaurantUser::truncate();
        Restaurant::truncate();
        Agency::truncate();
        City::truncate();
        Cuisine::truncate();

        // Only delete demo users, not all users
        User::where('email', 'like', '%@in.today.test')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Tables truncated.');
        $this->newLine();
    }

    private function createPlatformAdmin(): void
    {
        $this->info('Creating platform admin user...');

        // Use the same password for all demo users for consistency
        // Note: User model has 'password' => 'hashed' cast, so do NOT use Hash::make()
        $password = 'Demo123!';

        $admin = User::updateOrCreate(
            ['email' => 'admin@in.today.test'],
            [
                'name' => 'Platform Admin',
                'password' => $password,
                'global_role' => GlobalRole::PlatformAdmin,
                'email_verified_at' => now(),
            ]
        );

        $this->createdData['users'][] = [
            'email' => $admin->email,
            'password' => $password,
            'role' => 'Platform Admin',
            'access' => '/admin panel',
        ];
    }

    private function createCitiesAndCuisines(): void
    {
        $this->info('Creating cities and cuisines...');

        // Cities (country uses ISO 2-letter codes)
        $cities = [
            ['name' => 'Berlin', 'country' => 'DE', 'timezone' => 'Europe/Berlin', 'latitude' => 52.5200, 'longitude' => 13.4050],
            ['name' => 'Munich', 'country' => 'DE', 'timezone' => 'Europe/Berlin', 'latitude' => 48.1351, 'longitude' => 11.5820],
            ['name' => 'Athens', 'country' => 'GR', 'timezone' => 'Europe/Athens', 'latitude' => 37.9838, 'longitude' => 23.7275],
        ];

        foreach ($cities as $index => $cityData) {
            City::updateOrCreate(
                ['name' => $cityData['name']],
                array_merge($cityData, [
                    'is_active' => true,
                    'sort_order' => $index,
                ])
            );
        }

        // Cuisines
        $cuisines = [
            ['name_en' => 'Greek', 'name_de' => 'Griechisch', 'icon' => '🇬🇷'],
            ['name_en' => 'Italian', 'name_de' => 'Italienisch', 'icon' => '🇮🇹'],
            ['name_en' => 'Mediterranean', 'name_de' => 'Mediterran', 'icon' => '🌊'],
            ['name_en' => 'German', 'name_de' => 'Deutsch', 'icon' => '🇩🇪'],
        ];

        foreach ($cuisines as $index => $cuisineData) {
            Cuisine::updateOrCreate(
                ['name_en' => $cuisineData['name_en']],
                array_merge($cuisineData, ['sort_order' => $index])
            );
        }
    }

    private function createAgency(): Agency
    {
        $this->info('Creating demo agency...');

        $agency = Agency::updateOrCreate(
            ['slug' => 'berlin-hospitality-group'],
            [
                'name' => 'Berlin Hospitality Group',
                'contact_name' => 'Hans Mueller',
                'contact_email' => 'contact@bhg-demo.test',
                'contact_phone' => '+49 30 1234567',
                'billing_email' => 'billing@bhg-demo.test',
                'website_url' => 'https://bhg-demo.test',
                'settings' => [
                    'logo_url' => 'https://via.placeholder.com/200x60?text=BHG',
                    'primary_color' => '#1e40af',
                    'support_email' => 'support@bhg-demo.test',
                ],
                'is_active' => true,
            ]
        );

        $this->createdData['agencies']++;

        return $agency;
    }

    private function createRestaurants(Agency $agency): array
    {
        $this->info('Creating demo restaurants...');

        $berlin = City::where('name', 'Berlin')->first();
        $athens = City::where('name', 'Athens')->first();
        $greek = Cuisine::where('name_en', 'Greek')->first();
        $mediterranean = Cuisine::where('name_en', 'Mediterranean')->first();

        $restaurants = [];

        // Restaurant 1: Meraki Demo (linked to agency)
        $restaurants[] = Restaurant::updateOrCreate(
            ['slug' => 'meraki-demo'],
            [
                'name' => 'Meraki Demo',
                'agency_id' => $agency->id,
                'city_id' => $berlin->id,
                'cuisine_id' => $greek->id,
                'timezone' => 'Europe/Berlin',
                'country' => 'DE',
                'address_street' => 'Kantstraße 123',
                'address_district' => 'Charlottenburg',
                'address_postal' => '10623',
                'address_country' => 'DE',
                'latitude' => 52.5050,
                'longitude' => 13.3130,
                'price_range' => 3,
                'avg_rating' => 4.7,
                'review_count' => 234,
                'reservation_count' => 1520,
                'features' => ['outdoor_seating', 'wheelchair_accessible', 'live_music', 'private_dining'],
                'settings' => [
                    'default_duration' => 90,
                    'min_party_size' => 1,
                    'max_party_size' => 12,
                ],
                'plan' => RestaurantPlan::Pro,
                'is_active' => true,
                'is_verified' => true,
                'is_featured' => true,
            ]
        );

        // Restaurant 2: Platia Demo (direct customer, no agency)
        $restaurants[] = Restaurant::updateOrCreate(
            ['slug' => 'platia-demo'],
            [
                'name' => 'Platia Demo',
                'agency_id' => null,
                'city_id' => $berlin->id,
                'cuisine_id' => $mediterranean->id,
                'timezone' => 'Europe/Berlin',
                'country' => 'DE',
                'address_street' => 'Friedrichstraße 45',
                'address_district' => 'Mitte',
                'address_postal' => '10117',
                'address_country' => 'DE',
                'latitude' => 52.5170,
                'longitude' => 13.3889,
                'price_range' => 2,
                'avg_rating' => 4.5,
                'review_count' => 156,
                'reservation_count' => 890,
                'features' => ['outdoor_seating', 'vegetarian_options', 'family_friendly'],
                'settings' => [
                    'default_duration' => 75,
                    'min_party_size' => 1,
                    'max_party_size' => 8,
                ],
                'plan' => RestaurantPlan::Starter,
                'is_active' => true,
                'is_verified' => true,
                'is_featured' => false,
            ]
        );

        // Restaurant 3: Ouzeri Demo (in Athens, linked to agency)
        $restaurants[] = Restaurant::updateOrCreate(
            ['slug' => 'ouzeri-demo'],
            [
                'name' => 'Ouzeri Demo',
                'agency_id' => $agency->id,
                'city_id' => $athens->id,
                'cuisine_id' => $greek->id,
                'timezone' => 'Europe/Athens',
                'country' => 'GR',
                'address_street' => 'Adrianou 78',
                'address_district' => 'Plaka',
                'address_postal' => '10556',
                'address_country' => 'GR',
                'latitude' => 37.9755,
                'longitude' => 23.7275,
                'price_range' => 2,
                'avg_rating' => 4.8,
                'review_count' => 312,
                'reservation_count' => 2100,
                'features' => ['outdoor_seating', 'traditional_cuisine', 'live_music', 'sea_view'],
                'settings' => [
                    'default_duration' => 120,
                    'min_party_size' => 2,
                    'max_party_size' => 20,
                ],
                'plan' => RestaurantPlan::Business,
                'is_active' => true,
                'is_verified' => true,
                'is_featured' => true,
            ]
        );

        $this->createdData['restaurants'] = count($restaurants);

        return $restaurants;
    }

    private function createRestaurantUsers(array $restaurants): void
    {
        $this->info('Creating restaurant users...');

        // Note: User model has 'password' => 'hashed' cast, so do NOT use Hash::make()
        $password = 'Demo123!';

        // User A: Single restaurant owner (Meraki only)
        $userSingle = User::updateOrCreate(
            ['email' => 'owner.single@in.today.test'],
            [
                'name' => 'Single Owner Demo',
                'password' => $password,
                'global_role' => GlobalRole::User,
                'email_verified_at' => now(),
            ]
        );

        RestaurantUser::updateOrCreate(
            ['user_id' => $userSingle->id, 'restaurant_id' => $restaurants[0]->id],
            [
                'name' => $userSingle->name,
                'email' => $userSingle->email,
                'role' => RestaurantRole::Owner,
                'is_active' => true,
            ]
        );

        $this->createdData['users'][] = [
            'email' => $userSingle->email,
            'password' => $password,
            'role' => 'Restaurant Owner (single)',
            'access' => $restaurants[0]->name,
        ];

        // User B: Multi-restaurant owner (Meraki + Platia)
        $userMulti = User::updateOrCreate(
            ['email' => 'owner.multi@in.today.test'],
            [
                'name' => 'Multi Owner Demo',
                'password' => $password,
                'global_role' => GlobalRole::User,
                'email_verified_at' => now(),
            ]
        );

        RestaurantUser::updateOrCreate(
            ['user_id' => $userMulti->id, 'restaurant_id' => $restaurants[0]->id],
            [
                'name' => $userMulti->name,
                'email' => $userMulti->email,
                'role' => RestaurantRole::Owner,
                'is_active' => true,
            ]
        );

        RestaurantUser::updateOrCreate(
            ['user_id' => $userMulti->id, 'restaurant_id' => $restaurants[1]->id],
            [
                'name' => $userMulti->name,
                'email' => $userMulti->email,
                'role' => RestaurantRole::Manager,
                'is_active' => true,
            ]
        );

        $this->createdData['users'][] = [
            'email' => $userMulti->email,
            'password' => $password,
            'role' => 'Restaurant Owner/Manager (multi)',
            'access' => $restaurants[0]->name . ', ' . $restaurants[1]->name,
        ];

        // User C: Staff member (Platia only)
        $userStaff = User::updateOrCreate(
            ['email' => 'staff@in.today.test'],
            [
                'name' => 'Staff Demo',
                'password' => $password,
                'global_role' => GlobalRole::User,
                'email_verified_at' => now(),
            ]
        );

        RestaurantUser::updateOrCreate(
            ['user_id' => $userStaff->id, 'restaurant_id' => $restaurants[1]->id],
            [
                'name' => $userStaff->name,
                'email' => $userStaff->email,
                'role' => RestaurantRole::Staff,
                'is_active' => true,
            ]
        );

        $this->createdData['users'][] = [
            'email' => $userStaff->email,
            'password' => $password,
            'role' => 'Restaurant Staff',
            'access' => $restaurants[1]->name,
        ];
    }

    private function createOperationalData(array $restaurants): void
    {
        foreach ($restaurants as $restaurant) {
            $this->info("Creating operational data for {$restaurant->name}...");

            $this->createTables($restaurant);
            $this->createOpeningHours($restaurant);
            $this->createBlockedDates($restaurant);
            $this->createReservations($restaurant);
            $this->createWaitlistEntries($restaurant);
        }
    }

    private function createTables(Restaurant $restaurant): void
    {
        $zones = ['Main Floor', 'Terrace', 'Private Room'];
        $tableNumber = 1;

        foreach ($zones as $zone) {
            $tablesInZone = $zone === 'Private Room' ? 2 : 3;

            for ($i = 0; $i < $tablesInZone; $i++) {
                $seats = match ($zone) {
                    'Private Room' => rand(8, 12),
                    'Terrace' => rand(2, 4),
                    default => rand(2, 6),
                };

                Table::updateOrCreate(
                    ['restaurant_id' => $restaurant->id, 'name' => "Table {$tableNumber}"],
                    [
                        'zone' => $zone,
                        'seats' => $seats,
                        'min_guests' => 1,
                        'max_guests' => $seats,
                        'is_combinable' => $zone !== 'Private Room',
                        'is_active' => true,
                        'sort_order' => $tableNumber,
                    ]
                );

                $tableNumber++;
                $this->createdData['tables']++;
            }
        }
    }

    private function createOpeningHours(Restaurant $restaurant): void
    {
        // Standard schedule: Mon-Thu one shift, Fri-Sun two shifts
        $schedule = [
            0 => [['12:00', '22:00']], // Monday
            1 => [['12:00', '22:00']], // Tuesday
            2 => [['12:00', '22:00']], // Wednesday
            3 => [['12:00', '22:00']], // Thursday
            4 => [['12:00', '15:00'], ['18:00', '23:00']], // Friday
            5 => [['12:00', '15:00'], ['18:00', '23:00']], // Saturday
            6 => [['12:00', '21:00']], // Sunday
        ];

        foreach ($schedule as $dayOfWeek => $shifts) {
            foreach ($shifts as $index => $times) {
                $shiftName = count($shifts) > 1
                    ? ($index === 0 ? 'Lunch' : 'Dinner')
                    : null;

                OpeningHour::updateOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'day_of_week' => $dayOfWeek,
                        'shift_name' => $shiftName,
                    ],
                    [
                        'is_open' => true,
                        'open_time' => $times[0],
                        'close_time' => $times[1],
                        'last_reservation_time' => Carbon::createFromFormat('H:i', $times[1])->subHour()->format('H:i'),
                    ]
                );

                $this->createdData['opening_hours']++;
            }
        }
    }

    private function createBlockedDates(Restaurant $restaurant): void
    {
        $blockedDates = [
            [
                'date' => now()->addDays(14)->toDateString(),
                'is_all_day' => true,
                'reason' => 'Private Event - Wedding Reception',
            ],
            [
                'date' => now()->addMonth()->startOfMonth()->toDateString(),
                'is_all_day' => true,
                'reason' => 'Public Holiday - Closed',
            ],
            [
                'date' => now()->addDays(7)->toDateString(),
                'is_all_day' => false,
                'time_from' => '12:00',
                'time_to' => '15:00',
                'reason' => 'Private lunch event',
            ],
        ];

        foreach ($blockedDates as $blocked) {
            BlockedDate::updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'date' => $blocked['date'],
                ],
                $blocked
            );

            $this->createdData['blocked_dates']++;
        }
    }

    private function createReservations(Restaurant $restaurant): void
    {
        $tables = $restaurant->tables()->get();
        $statuses = [
            ReservationStatus::Pending,
            ReservationStatus::Confirmed,
            ReservationStatus::Confirmed,
            ReservationStatus::Confirmed,
            ReservationStatus::Completed,
            ReservationStatus::Completed,
            ReservationStatus::NoShow,
            ReservationStatus::CancelledByCustomer,
            ReservationStatus::CancelledByRestaurant,
        ];

        $sources = [
            ReservationSource::Platform,
            ReservationSource::Platform,
            ReservationSource::Widget,
            ReservationSource::Phone,
            ReservationSource::WalkIn,
        ];

        $firstNames = ['Emma', 'Liam', 'Sophia', 'Noah', 'Olivia', 'Lucas', 'Mia', 'Ethan', 'Isabella', 'Alexander'];
        $lastNames = ['Schmidt', 'Mueller', 'Schneider', 'Fischer', 'Weber', 'Meyer', 'Wagner', 'Becker', 'Hoffmann', 'Koch'];

        // Create reservations for past week, today, and next few days
        $dates = [
            now()->subDays(5),
            now()->subDays(3),
            now()->subDays(1),
            now(),
            now()->addDay(),
            now()->addDays(2),
            now()->addDays(3),
            now()->addDays(5),
            now()->addDays(7),
            now()->addDays(10),
        ];

        foreach ($dates as $date) {
            $reservationsPerDay = rand(1, 3);

            for ($i = 0; $i < $reservationsPerDay; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $status = $statuses[array_rand($statuses)];
                $source = $sources[array_rand($sources)];

                // Past reservations should be completed or no-show
                if ($date->isPast()) {
                    $status = rand(0, 10) > 2 ? ReservationStatus::Completed : ReservationStatus::NoShow;
                }

                // Future reservations should be pending or confirmed
                if ($date->isFuture()) {
                    $status = rand(0, 10) > 3 ? ReservationStatus::Confirmed : ReservationStatus::Pending;
                }

                $hour = rand(12, 20);
                $minute = [0, 15, 30, 45][rand(0, 3)];
                $guests = rand(2, 6);
                $table = $tables->random();

                Reservation::create([
                    'restaurant_id' => $restaurant->id,
                    'table_id' => $table->id,
                    'customer_name' => "{$firstName} {$lastName}",
                    'customer_email' => strtolower("{$firstName}.{$lastName}@example.com"),
                    'customer_phone' => '+49 ' . rand(100, 999) . ' ' . rand(1000000, 9999999),
                    'date' => $date->toDateString(),
                    'time' => sprintf('%02d:%02d', $hour, $minute),
                    'guests' => $guests,
                    'duration_minutes' => 90,
                    'status' => $status,
                    'source' => $source,
                    'customer_notes' => rand(0, 3) === 0 ? 'Window seat preferred' : null,
                    'internal_notes' => rand(0, 5) === 0 ? 'VIP guest' : null,
                    'language' => ['en', 'de', 'el'][rand(0, 2)],
                    'confirmed_at' => $status === ReservationStatus::Confirmed ? now() : null,
                    'completed_at' => $status === ReservationStatus::Completed ? $date->copy()->setTime($hour + 2, 0) : null,
                    'cancelled_at' => $status->isCancelled() ? now() : null,
                ]);

                $this->createdData['reservations']++;
            }
        }
    }

    private function createWaitlistEntries(Restaurant $restaurant): void
    {
        $statuses = [
            WaitlistStatus::Waiting,
            WaitlistStatus::Waiting,
            WaitlistStatus::Notified,
            WaitlistStatus::Converted,
            WaitlistStatus::Expired,
        ];

        $firstNames = ['Anna', 'Max', 'Julia', 'Felix', 'Marie', 'Paul'];
        $lastNames = ['Braun', 'Klein', 'Wolf', 'Schwarz', 'Richter', 'Lange'];

        // Create waitlist entries for upcoming busy nights
        $dates = [
            now()->next('Friday'),
            now()->next('Saturday'),
            now()->addWeeks(2)->next('Saturday'),
        ];

        foreach ($dates as $date) {
            $entriesPerDay = rand(2, 4);

            for ($i = 0; $i < $entriesPerDay; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $status = $statuses[array_rand($statuses)];

                $hour = rand(18, 20);
                $minute = [0, 30][rand(0, 1)];

                Waitlist::create([
                    'restaurant_id' => $restaurant->id,
                    'customer_name' => "{$firstName} {$lastName}",
                    'customer_email' => strtolower("{$firstName}.{$lastName}@example.com"),
                    'customer_phone' => '+49 ' . rand(100, 999) . ' ' . rand(1000000, 9999999),
                    'date' => $date->toDateString(),
                    'preferred_time' => sprintf('%02d:%02d', $hour, $minute),
                    'guests' => rand(2, 6),
                    'status' => $status,
                    'notified_at' => $status === WaitlistStatus::Notified ? now() : null,
                    'expires_at' => $date->copy()->setTime(23, 59),
                ]);

                $this->createdData['waitlist']++;
            }
        }
    }

    private function printSummary(): void
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('                    DEMO DATA SEEDING COMPLETE                  ');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        $this->info('📊 CREATED DATA:');
        $this->table(
            ['Type', 'Count'],
            [
                ['Agencies', $this->createdData['agencies']],
                ['Restaurants', $this->createdData['restaurants']],
                ['Tables', $this->createdData['tables']],
                ['Opening Hours', $this->createdData['opening_hours']],
                ['Blocked Dates', $this->createdData['blocked_dates']],
                ['Reservations', $this->createdData['reservations']],
                ['Waitlist Entries', $this->createdData['waitlist']],
            ]
        );

        $this->newLine();
        $this->info('👤 USER ACCOUNTS:');
        $this->newLine();

        $headers = ['Email', 'Password', 'Role', 'Access'];
        $rows = [];

        foreach ($this->createdData['users'] as $user) {
            $rows[] = [
                $user['email'],
                $user['password'],
                $user['role'],
                $user['access'],
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info('🔗 LOGIN URLS:');
        $this->info('   Platform Admin: ' . url('/admin'));
        $this->info('   Business Panel: ' . url('/business'));
        $this->newLine();

        $this->warn('⚠️  Remember: These credentials are for DEVELOPMENT ONLY!');
        $this->newLine();
    }
}
