<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ValidateTokenRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->sendResetLink(
            $request->validated('email')
        );

        return ApiResponse::success(
            null,
            'If that email exists, we have sent a reset link.'
        );
    }

    public function validateToken(ValidateTokenRequest $request): JsonResponse
    {
        $this->authService->validateResetToken(
            $request->validated('token')
        );

        return ApiResponse::success(null, 'Token is valid.');
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword(
            $request->validated()
        );

        return ApiResponse::success(
            null,
            'Password has been reset successfully.'
        );
    }
}