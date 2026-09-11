<?php

namespace App\Services\Tokens;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Registered;

class EmailVerificationService extends RedisTokenService
{
    const PREFIX       = 'email_verify:';
    const INDEX_PREFIX = 'email_verify_idx:';
    const TTL          = 3600; // 1 Hours

    protected function getPrefix(): string      { return self::PREFIX; }
    protected function getIndexPrefix(): string { return self::INDEX_PREFIX; }
    protected function getTtl(): int            { return self::TTL; }

    public function sendVerificationLink(array $data): void
    {
        // Validasi email belum terdaftar
        if (User::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email already registered.'],
            ]);
        }

        $token = $this->storeToken($data['email'], [
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Notification::route('mail', $data['email'])
            ->notify(new VerifyEmailNotification($token));
    }

    public function verifyAndCreateUser(string $token): User
    {
        $payload = $this->consumeToken($token);

        $user = User::create([
            'email'                => $payload['email'],
            'password'             => $payload['password'],
            'email_verified_at'    => now(),
            'profile_completed_at' => null,
        ]);

        $user->assignRole('buyer');

        event(new Registered($user));

        return $user;
    }
}