# Laravel API Development Guidelines

## Project Context

- **Framework:** Laravel 11.x
- **Language:** PHP 8.2+ (Strict typing enabled)
- **Architecture:** Headless REST API (No Blade views).
- **Business Logic:** Keep controllers thin. Extract business logic to `App\Services` or `App\Actions`.

## Coding Standards

- Declare strict types (`declare(strict_types=1);`) at the top of all PHP files.
- Always use explicit return types (e.g., `: JsonResponse`, `: ResourceCollection`).
- Use single responsibility controllers (invokable) when possible.

## Data Flow

- **Input:** Always use Form Requests (`App\Http\Requests`) for validation. Never validate in the controller.
- **Output:** Always return responses using API Resources (`App\Http\Resources`). Do not return Eloquent models directly.
- **HTTP Status Codes:** Use 200 for OK, 201 for Created, 204 for No Content, 403 for Forbidden, and 422 for Validation Errors.

## Eloquent & Database

- Always prevent N+1 query problems by using eager loading (`->with()`).
- Use database transactions for multi-step write operations.
- Avoid raw SQL; rely on Eloquent ORM and Query Builder.

## Security

- Use Laravel Sanctum for API authentication.
- Always check authorization using standard Laravel Policies (`$this->authorize()`) before executing resource modifications.

## Roles & Permissions (Spatie)

- **Package:** We use `spatie/laravel-permission` for all role-based access control (RBAC).
- **Implementation:** Do not write custom logic for roles. Always use Spatie's built-in methods (e.g., `$user->hasPermissionTo('create appointments')`).
- **Route Protection:** Protect API endpoints using Spatie's middleware (`role`, `permission`, or `role_or_permission`) in the `api.php` routes file.
- **Policies:** When generating Laravel Policies, wrap Spatie's permission checks inside the policy methods to keep authorization centralized.
- **Seeding:** Always generate seeders for newly created roles and permissions.
