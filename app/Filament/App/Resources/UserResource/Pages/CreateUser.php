<?php

namespace App\Filament\App\Resources\UserResource\Pages;

use App\Filament\App\Resources\UserResource;
use App\Mail\WelcomeUserMail;
use App\Models\Organization;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected $plainPassword;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $password            = $this->generateRandomPassword(10);
        $this->plainPassword = $password;

        $data['password']          = bcrypt($password);
        $data['email_verified_at'] = now();
        $data['is_tenant_admin']   = false;

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        // Find the organization associated with the user
        $organization = Organization::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();

        // If organization is found, send the email
        if ($organization) {
            // Send welcome email with organization name
            Mail::to($user->email)->queue(new WelcomeUserMail($user->name, $this->plainPassword, $organization->name));
        }
    }
    // Function to generate random password
    protected function generateRandomPassword($length = 10)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_-+=<>?';

        return substr(str_shuffle($characters), 0, $length);
    }

    // Return to users list
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
