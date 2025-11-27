<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\GlobalRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreatePlatformAdminCommand extends Command
{
    protected $signature = 'app:create-platform-admin
                            {--email=admin@in.today : Email address for the admin}
                            {--name=Super Admin : Name for the admin}';

    protected $description = 'Create a platform admin user with a random password';

    public function handle(): int
    {
        $email = $this->option('email');
        $name = $this->option('name');

        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists.");

            return self::FAILURE;
        }

        $password = Str::password(16);

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'global_role' => GlobalRole::PlatformAdmin,
        ]);

        $this->info('Platform admin created successfully!');
        $this->newLine();
        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Password', $password],
                ['Role', $user->global_role->value],
            ]
        );
        $this->newLine();
        $this->warn('⚠️  Save this password now! It will not be shown again.');

        return self::SUCCESS;
    }
}
