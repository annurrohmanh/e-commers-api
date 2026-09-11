<?php

namespace App\Services;

use App\Models\User;
use App\Services\Tokens\PasswordResetService;
use App\Services\Tokens\EmailVerificationService;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str; // FIX 1: Import Str yang sebelumnya hilang
use Tymon\JWTAuth\JWTAuth;

class AuthService
{
    public function __construct(
        private readonly AuthManager              $auth,
        private readonly JWTAuth                  $jwt,
        private readonly RedisFactory             $redis,
        private readonly PasswordResetService     $passwordReset,
        private readonly EmailVerificationService $emailVerification, 
    ) {}

    private function connection()
    {
        return $this->redis->connection('default');
    }

    public function register(array $data): void
    {
        $this->emailVerification->sendVerificationLink($data);
    }

    public function completeProfile(User $user, array $data): User
    {
        if ($user->profile_completed_at !== null) {
            throw ValidationException::withMessages([
                'profile' => ['Profile already completed.'],
            ]);
        }

        $user->update([
            ...$data,
            'profile_completed_at' => now(),
        ]);

        return $user->fresh();
    }

    public function login(array $credentials): array
    {
        if (! $token = $this->auth->guard('api')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user         = $this->auth->guard('api')->user();
        $refreshToken = $this->storeRefreshToken($user);

        return $this->tokenResponse($token, $refreshToken);
    }

    public function refresh(string $refreshToken): array
    {
        $tokenHash  = hash('sha256', $refreshToken);
        $connection = $this->connection();

        // FIX: Langsung ambil User ID berdasarkan hash token (Sangat cepat & aman dari prefix)
        $userId = $connection->get('refresh_token:' . $tokenHash);

        if (! $userId) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Invalid or expired refresh token.'],
            ]);
        }

        $user = User::query()->find($userId);

        if (! $user) {
            throw ValidationException::withMessages([
                'refresh_token' => ['Invalid or expired refresh token.'],
            ]);
        }

        // Hapus token lama
        $connection->del('refresh_token:' . $tokenHash);

        // Buat token baru
        $accessToken     = $this->jwt->fromUser($user);
        $newRefreshToken = $this->storeRefreshToken($user);

        return $this->tokenResponse($accessToken, $newRefreshToken);
    }

    public function logout(string $accessToken, string $refreshToken): void
    {
        $this->jwt->setToken($accessToken);

        try {
            $payload      = $this->jwt->getPayload();
            $jti          = $payload->get('jti');
            $exp          = $payload->get('exp');
            $remainingTtl = max(1, $exp - time());

            $this->connection()->setex('blacklist:' . $jti, $remainingTtl, '1');
        } catch (\Throwable) {
            // Token mungkin sudah tidak valid, abaikan blacklist
        }

        // FIX: Hapus token refresh secara langsung tanpa looping KEYS
        if ($refreshToken) {
            $tokenHash = hash('sha256', $refreshToken);
            $this->connection()->del('refresh_token:' . $tokenHash);
        }
    }

    public function me(): User
    {
        /** @var User $user */
        $user = $this->auth->guard('api')->user();

        return $user->load(['roles', 'permissions']);
    }

    public function sendResetLink(string $email): void
    {
        $this->passwordReset->sendResetLink($email);
    }

    public function verifyEmail(string $token): array
    {
        $user = $this->emailVerification->verifyAndCreateUser($token);

        // Auto login setelah email sukses diverifikasi
        $accessToken  = $this->jwt->fromUser($user);
        $refreshToken = $this->storeRefreshToken($user);

        return $this->tokenResponse($accessToken, $refreshToken);
    }

    public function resetPassword(array $data): void
    {
        $this->passwordReset->resetPassword($data);
    }

    public function validateResetToken(string $token): array  // ✅ tambah ini
    {
        return $this->passwordReset->validateResetToken($token);
    }

    private function storeRefreshToken(User $user): string
    {
        $refreshToken = Str::random(64);
        $tokenHash    = hash('sha256', $refreshToken);
        $ttlSeconds   = (int) config('jwt.refresh_ttl', 20160) * 60;

        // FIX: Simpan User ID sebagai VALUE, bukan sebagai bagian dari nama KEY
        $this->connection()->setex(
            'refresh_token:' . $tokenHash,
            $ttlSeconds,
            (string) $user->id // Menyimpan ID user di sini
        );

        return $refreshToken;
    }

    private function tokenResponse(string $accessToken, string $refreshToken): array
    {
        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'bearer',
            'expires_in'    => (int) config('jwt.ttl', 60) * 60, // Fallback jika config null
        ];
    }
}