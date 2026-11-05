<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Users\UpdateUserProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * Actualiza la información del perfil del usuario autenticado.
     */
    public function update(UpdateUserProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $updatedUser = $this->userService->updateProfile($user, $request->validated());

        return response()->json([
            'message' => 'Perfil de usuario actualizado correctamente',
            'user' => new UserResource($updatedUser),
        ], 200);
    }
}

