<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Valida credenciales, genera token Sanctum y registra auditoría.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        activity('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->log('Inicio de sesión exitoso');

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Registra un nuevo usuario en el sistema y le emite un token de acceso.
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make((string) $data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        activity('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->log('Usuario registrado');

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Renueva el token de acceso actual del usuario.
     */
    public function refreshToken(User $user): string
    {
        $user->currentAccessToken()?->delete();

        return $user->createToken('auth_token')->plainTextToken;
    }

    /**
     * Invalida el token actual cerrando la sesión.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();

        activity('auth')
            ->performedOn($user)
            ->causedBy($user)
            ->log('Cierre de sesión');
    }
}
