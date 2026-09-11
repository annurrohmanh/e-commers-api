<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\CompleteProfileRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException; // DI-IMPORT UNTUK FILTER CATCH

class RegisterController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function register(RegisterUserRequest $request): JsonResponse
    {
        try {
            $this->authService->register($request->validated());

            return ApiResponse::success(
                null,
                'Registration successful. Please check your email to verify your account.'
            );
        } catch (ValidationException $exception) {
            // Biarkan ValidationException lolos ke handler Laravel agar menghasilkan status 422
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Register failed', ['exception' => $exception]);

            return ApiResponse::error('Unable to register.', null, 500);
        }
    }

    public function verify(string $token): JsonResponse
    {
        try {
            $tokens = $this->authService->verifyEmail($token);

            return $this->respondWithCookies($tokens, 'Email verified successfully.');
        } catch (ValidationException $exception) {
            // Biarkan pesan "Token expired or invalid" dikembalikan dengan status 422
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error('Email verification failed', ['exception' => $exception]);

            return ApiResponse::error('Unable to verify email.', null, 500);
        }
    }

    public function completeProfile(CompleteProfileRequest $request): JsonResponse
    {
        // Method ini aman karena tidak dibungkus try-catch yang agresif,
        // sehingga jika throw ValidationException langsung otomatis mengembalikan 422
        $user = $this->authService->completeProfile(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            new UserResource($user),
            'Profile completed'
        );
    }

    private function respondWithCookies(array $tokens, string $message): JsonResponse
    {
        $isProduction = app()->environment('production');
        $refreshTtl   = (int) config('jwt.refresh_ttl');

        return ApiResponse::success([
            'access_token' => $tokens['access_token'],
            'token_type'   => $tokens['token_type'],
            'expires_in'   => $tokens['expires_in'],
        ], $message)
        ->cookie(
            'refresh_token',
            $tokens['refresh_token'],
            $refreshTtl,
            '/',
            null,
            $isProduction,
            true,
            false,
            'Strict'
        );
    }
}