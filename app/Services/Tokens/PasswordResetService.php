<?php

namespace App\Services\Tokens;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;

class PasswordResetService extends RedisTokenService
{
    const PREFIX       = 'pwd_reset:';
    const INDEX_PREFIX = 'pwd_reset_idx:';
    const TTL          = 900; // 15 minutes

    protected function getPrefix(): string      { return self::PREFIX; }
    protected function getIndexPrefix(): string { return self::INDEX_PREFIX; }
    protected function getTtl(): int            { return self::TTL; }

    public function sendResetLink(string $email): void
    {
        $user = User::where('email', $email)->first();
        if (! $user) return;

        $token = $this->storeToken($email, ['email' => $email]);

        $user->notify(new ResetPasswordNotification($token));
    }
    

    public function validateResetToken(string $token): array
    {
        return $this->peekToken($token);  // ✅ hanya baca, token tidak terhapus
    }

    public function resetPassword(array $data): void
    {
        $payload = $this->consumeToken($data['token']);  // ✅ baru dihapus saat reset
        $user    = User::where('email', $payload['email'])->firstOrFail();

        $user->update(['password' => Hash::make($data['password'])]);

        event(new PasswordReset($user));
    }
}