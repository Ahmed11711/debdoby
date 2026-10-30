<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestApi\Auth\ForgotPasswordRequest;
use App\Http\Requests\RequestApi\Auth\LoginRequest;
use App\Http\Requests\RequestApi\Auth\RegisterRequest;
use App\Http\Requests\RequestApi\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\DabdobyMoodService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Register a new user and return a JWT token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = auth('api')->login($user);

        return $this->tokenResponse(
            $token,
            $user,
            'Registered successfully',
            201
        );
    }

    /**
     * Login and return a JWT token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = auth('api')->attempt(
            $request->only('email', 'password')
        );

        if (! $token) {
            return $this->errorResponse(
                'Invalid email or password',
                401
            );
        }

        $user = auth('api')->user();

        return $this->tokenResponse(
            $token,
            $user,
            'Logged in successfully'
        );
    }

    /**
     * Get the authenticated user.
     */
    public function me(DabdobyMoodService $mood): JsonResponse
    {
        $user = auth('api')->user();

        if (! $user) {
            return $this->errorResponse(
                'Unauthorized',
                401
            );
        }

        $data = [
            'user' => $user,
            'gift_giver' => $user->giftGivers()
                ->latest('id')
                ->first(['id', 'name']),
            'mood' => $mood->for($user),
            'last_voice_at' => $user->voiceMessages()
                ->latest('id')
                ->value('created_at'),
        ];

        return $this->successResponse(
            $data,
            'User retrieved successfully',
            200
        );
    }

    /**
     * Refresh the JWT token.
     */
    public function refresh(): JsonResponse
    {
        $token = auth('api')->refresh();
        $user = auth('api')->user();

        return $this->tokenResponse(
            $token,
            $user,
            'Token refreshed'
        );
    }

    /**
     * Logout and invalidate the current token.
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return $this->messageResponse(
            'Logged out successfully',
            200
        );
    }

    /**
     * Send a password reset link.
     */
    public function forgotPassword(
        ForgotPasswordRequest $request
    ): JsonResponse {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->errorResponse(
                __($status),
                400
            );
        }

        return $this->successResponse(
            null,
            __($status),
            200
        );
    }

    /**
     * Reset the password using the emailed token.
     */
    public function resetPassword(
        ResetPasswordRequest $request
    ): JsonResponse {
        $status = Password::reset(
            $request->only(
                'email',
                'password',
                'password_confirmation',
                'token'
            ),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->errorResponse(
                __($status),
                400
            );
        }

        return $this->successResponse(
            null,
            __($status),
            200
        );
    }

    /**
     * Return a consistent JWT response.
     */
    protected function tokenResponse(
        string $token,
        User $user,
        string $message,
        int $status = 200
    ): JsonResponse {
        return $this->successResponse(
            [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ],
            $message,
            $status
        );
    }
}
