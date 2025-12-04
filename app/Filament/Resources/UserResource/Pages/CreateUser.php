<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Generate a random password if none is provided.
     *
     * When creating users from the admin panel, we don't require a password
     * in the form. Instead, we generate a strong random password automatically.
     * The user can reset their password via email later.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['password'])) {
            // Generate a strong random password (will be hashed by User model's cast)
            $data['password'] = Str::random(32);
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
