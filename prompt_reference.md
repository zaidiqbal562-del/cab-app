# Laravel Cab App — Project Handover / AI Continuation Reference

> Purpose: Paste this document into another AI assistant to continue development on the same project.
> Last updated: 23 October 2026
> User preference: Explain in simple Hinglish, beginner-friendly, step-by-step. Prefer exact file paths and terminal commands.

## 1. Project overview

We are building a cab-booking application as a **single Laravel monolith** (one codebase, not separate customer/driver/admin applications).

Roles:
- Customer: book rides and view their rides.
- Driver: manage availability, receive ride offers, accept rides, update ride status/location, view earnings.
- Admin: manage users/drivers/rides and view operational stats.

Project path on the original laptop:
`~/Desktop/cab-app`

The app is intended to become a ride-hailing system with ride matching, offer timeouts, live driver location, queues, and real-time updates.

## 2. Development environment / setup known so far

Commands already run:
```bash
composer create-project laravel/laravel cab-app
cd ~/Desktop/cab-app
php artisan install:broadcasting
```
During broadcasting installation, Laravel Reverb was selected. Node dependencies were installed and built:
```bash
npm install
npm run build
```

Laravel Breeze was installed for Blade authentication:
```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
```

Environment observed previously:
- PHP 8.4.26
- PsySH / Laravel Tinker available
- OS on old laptop: Ubuntu Linux
- Database driver/credentials have NOT been recorded in this handover; inspect `.env` on the new machine.
- Reverb installation succeeded, but live broadcasting configuration and end-to-end event delivery still need verification.

Sanctum was discussed, but there is no confirmed evidence that it was installed. Check before assuming:
```bash
composer show laravel/sanctum
```

## 3. Authentication and roles

### User model

File: `app/Models/User.php`

Current known code:
```php
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

Important:
- `role` is in the model's `Fillable` attribute.
- Do NOT add `'role' => 'admin|customer|driver'` to `casts()`. That is not a valid cast. `casts()` is for data types such as `datetime`, `boolean`, `array`, etc.
- The `password => hashed` cast means assign the plain password in model creation and Laravel hashes it automatically. Avoid double-hashing via `Hash::make()` when using this cast unless intentionally handling it.
- Verify actual current file before editing, as code may have changed since this handover.

### Role database migration

A migration was created and run to add a `role` column to `users`:
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('role')->default('customer')->index();
});
```
Its `down()` method drops the `role` column.

Verify on the new machine:
```bash
php artisan migrate:status
```
If needed, inspect the migrations folder for the migration that adds `role`.

### Role middleware

File: `app/Http/Middleware/RoleMiddleware.php`

Known code:
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user() || $request->user()->role !== $role) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}
```

Alias `role` was registered in `bootstrap/app.php` using Laravel's `withMiddleware` configuration. Verify that alias is present in the current file.

### Admin account

A user was created using Laravel Tinker with:
- Name: `Admin`
- Email: `admin@example.com`
- Password used during local setup: `Admin@12345`
- Role: `admin`

The initial creation attempt did not create a retrievable user; a later one-line Tinker command successfully created it, according to the user. The user reported “done bna gya.” Still verify it on the new laptop/database because a fresh database will not automatically contain this record.

Useful commands:
```bash
php artisan tinker
```
Inside Tinker, enter each line separately:
```php
App\Models\User::where('email', 'admin@example.com')->value('role');
```
Expected role if the same database is in use:
```text
"admin"
```
If it returns `null`, the record is absent in the current database. To create it, after confirming you are connected to the intended local/dev database:
```php
App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => 'Admin@12345',
    'role' => 'admin',
]);
```
Because the model has a `hashed` password cast, Laravel hashes the password.
Security note: change this development password before any deployment, and never commit production secrets.

## 4. Public registration security

The registration form was edited to include a role dropdown with only:
```html
<option value="customer">Customer</option>
<option value="driver">Driver</option>
```
File: `resources/views/auth/register.blade.php`

There should NOT be a public Admin option.

The registration controller is expected to validate role strictly:
```php
'role' => ['required', 'in:customer,driver'],
```
File likely: `app/Http/Controllers/Auth/RegisteredUserController.php`

The controller change is NOT confirmed as fully applied. Check its `store()` method before assuming role registration works. Preserve Breeze's existing validation, `Registered` event, login behavior, and any other existing behavior while making changes. Never trust a role value sent by the browser without server-side validation.

## 5. Login redirect by role — current work / verify status

Goal:
- Customer login → `/customer`
- Driver login → `/driver`
- Admin login → `/admin`

Breeze login controller:
`app/Http/Controllers/Auth/AuthenticatedSessionController.php`

Suggested `store()` method change after successful authentication/session regeneration:
```php
$request->session()->regenerate();

return match ($request->user()->role) {
    'admin' => redirect()->route('admin.dashboard'),
    'driver' => redirect()->route('driver.dashboard'),
    default => redirect()->route('customer.dashboard'),
};
```

This change was recently recommended, but the user has NOT yet confirmed that it was applied or tested. Inspect current file and implement if missing. Keep Breeze's authentication validation and session handling intact.

## 6. Routes

File: `routes/web.php`

Desired named routes, protected by both `auth` and role middleware:
```php
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        Route::view('/', 'customer.dashboard')->name('dashboard');
    });

Route::middleware(['auth', 'role:driver'])
    ->prefix('driver')
    ->name('driver.')
    ->group(function () {
        Route::view('/', 'driver.dashboard')->name('dashboard');
    });

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');
    });
```

These routes were suggested, but current `routes/web.php` has not been shared after the change. Verify and avoid duplicate route definitions. Keep Breeze auth route inclusion, often `require __DIR__.'/auth.php';`.

If a `/dashboard` route is used as the post-login landing route, it can redirect by role, but if the login controller directly redirects to the named role dashboard routes above, that can be sufficient. Do not leave conflicting duplicate routes.

Verify:
```bash
php artisan route:list
```
Expected named routes:
- `customer.dashboard`
- `driver.dashboard`
- `admin.dashboard`

Useful cache clear:
```bash
php artisan optimize:clear
```

## 7. Dashboard Blade views created

These Blade views were created as UI placeholders:

### Customer
`resources/views/customer/dashboard.blade.php`
- Uses `<x-app-layout>`.
- Basic customer ride-booking form UI.
- Pickup and destination inputs.
- Placeholder booking button.
- Placeholder activity/stats.

### Driver
`resources/views/driver/dashboard.blade.php`
- Uses `<x-app-layout>`.
- Online/offline placeholder.
- Earnings placeholder.
- Ride requests placeholder.

### Admin
`resources/views/admin/dashboard.blade.php`
- Uses `<x-app-layout>`.
- Overview stats placeholders.
- Recent rides table placeholder.
- Driver approval placeholder.

Important: These are UI-only placeholders so far. Ride booking, driver matching, online/offline persistence, earnings calculations, admin approval actions, and ride records have NOT been confirmed as functional.

## 8. Broadcasting / Reverb

Laravel broadcasting was installed through:
```bash
php artisan install:broadcasting
```
Laravel Reverb was selected and Node dependencies were installed/built.

Intended future uses:
- Notify nearby/eligible drivers about ride offers.
- Broadcast ride accepted/status changes to the customer.
- Broadcast driver location changes.
- Real-time admin operational updates if needed.

Still to do:
- Inspect `.env` broadcasting/Reverb variables.
- Confirm `config/broadcasting.php` and Reverb config.
- Configure the Reverb server and run it locally.
- Implement and test event broadcasting/authenticated private channels.
- Ensure events implement the correct broadcasting interfaces and authorization is secure.

Do not assume real-time functionality is already working just because the installer ran.

## 9. Planned domain models / architecture

Models discussed for the cab app:
- `User`
- `Driver`
- `Vehicle`
- `Ride`
- `RideOffer`
- `Payment`

Events discussed:
- `RideOffered`
- `RideAccepted`
- `DriverLocationUpdated`

Queue job discussed:
- `CheckOfferTimeout`

These domain models/events/jobs may be plans only; their files and migrations have not been confirmed as created. Inspect the project before generating duplicates.

Potential responsibilities:
- `User`: authentication, name/email/password, role.
- `Driver`: driver-specific profile, approval status, online status, current location (or use separate location tracking table if appropriate).
- `Vehicle`: vehicle details owned/assigned to a driver.
- `Ride`: customer, pickup/destination coordinates and addresses, status, assigned driver, fare and lifecycle timestamps.
- `RideOffer`: a ride offered to a driver, offer status, expiry timestamp.
- `Payment`: ride payment intent/amount/provider/status and references (avoid storing raw card details).
- `RideOffered`: real-time offer notification.
- `RideAccepted`: notify customer and relevant services.
- `DriverLocationUpdated`: publish location to authorized viewers.
- `CheckOfferTimeout`: expire unaccepted offers and continue matching.

Design and implement after inspecting current files/schema, not by assuming they already exist.

## 10. Known project files and what they do

| File/path | Purpose / current understanding |
|---|---|
| `app/Models/User.php` | User model; fillable role and hashed password cast |
| `app/Http/Middleware/RoleMiddleware.php` | Restricts access to a role |
| `bootstrap/app.php` | Middleware alias registration (`role`) |
| `app/Http/Controllers/Auth/RegisteredUserController.php` | Breeze registration; check role validation/assignment |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | Breeze login; implement role-based redirect here |
| `resources/views/auth/register.blade.php` | Register form with Customer/Driver role options |
| `resources/views/customer/dashboard.blade.php` | Customer dashboard UI placeholder |
| `resources/views/driver/dashboard.blade.php` | Driver dashboard UI placeholder |
| `resources/views/admin/dashboard.blade.php` | Admin dashboard UI placeholder |
| `routes/web.php` | App routes and role-protected dashboard routes; verify names and avoid duplicates |
| `routes/auth.php` | Breeze authentication routes, typically included by `web.php` |
| `.env` | Local database and Reverb credentials/config; never share secrets publicly |
| `database/migrations/` | User role migration and Laravel's base schema; inspect for domain model migrations |

## 11. Setup on another laptop

1. Install compatible PHP, Composer, Node.js/npm, and the required database server.
2. Clone/copy the repository. The local path on the old laptop was `~/Desktop/cab-app`, but use any path on the new machine.
3. In the project directory:
```bash
composer install
npm install
```
4. Create local environment config:
```bash
cp .env.example .env
php artisan key:generate
```
5. Edit `.env` to set the new machine's database credentials and any local Reverb values. Do not copy production secrets into shared AI prompts.
6. Create the database, then run:
```bash
php artisan migrate
npm run build
```
7. Run app:
```bash
php artisan serve
```
8. If needed in another terminal:
```bash
npm run dev
```
9. For Reverb, first inspect installed config/scripts and `.env`; run the documented Reverb command only after confirming config, commonly:
```bash
php artisan reverb:start
```
10. Create a local admin account using Tinker as described above, if the current database has no admin.

If source code is not in Git or not pushed, this project will not automatically appear on the other laptop. Commit/push it to a private repository or transfer the project directory securely. Never commit `.env`.

## 12. Immediate checklist / next tasks

### Priority 1 — Verify role-based auth
- [ ] Inspect `User.php` and confirm role is fillable; `casts()` has only real casts.
- [ ] Verify role column exists in current DB.
- [ ] Verify `RoleMiddleware` exists and `role` alias is registered in `bootstrap/app.php`.
- [ ] Verify `RegisteredUserController` validates role as only `customer,driver` and assigns it.
- [ ] Verify login controller redirects based on role after session regeneration.
- [ ] Verify named role dashboard routes exist exactly once and use role middleware.
- [ ] Test customer, driver, and admin login separately.
- [ ] Test that a customer cannot open `/admin`, and a driver cannot open `/customer` (expect 403 or safe redirect according to chosen UX).

### Priority 2 — Make registration/admin provisioning safe
- [ ] Keep admin unavailable from public registration.
- [ ] Change development admin password before any deployment.
- [ ] Consider a secure seeder or command for local admin creation.

### Priority 3 — Real ride-booking flow
- [ ] Design schema and migrations for Driver, Vehicle, Ride, RideOffer, Payment after checking current project.
- [ ] Implement request validation, relationships, ride statuses, and authorization policies.
- [ ] Customer can create a ride request and see ride status.
- [ ] Driver availability and approval flow.
- [ ] Matching algorithm (nearby eligible drivers, offer expiry, next-driver fallback).
- [ ] Queue job for offer timeout and configured queue worker.
- [ ] Reverb events for ride offers/acceptance/location.
- [ ] Admin views/actions for driver approvals and ride/user management.
- [ ] Payment provider integration later; never store raw card data.

## 13. Instructions for the next AI assistant

Please continue from this project reference and help me in simple Hinglish, one step at a time.

Before changing files:
1. Ask me for the current content of relevant files, or have me run commands to inspect them.
2. Do not assume suggested changes above were applied if marked unconfirmed.
3. Do not overwrite full Breeze controllers/routes blindly; preserve existing behavior.
4. Give exact paths and commands, explain what each step does, and ask me to share exact errors if a step fails.
5. Keep the app as one Laravel monolith with role-based customer/driver/admin pages.
6. Keep security in mind: public users cannot register as admin, role routes must be server-side protected, validate every ride request, and never expose `.env` secrets.
7. First finish and test role-based login redirect, then proceed to the real ride-booking backend.

## 14. Important security note

The local setup used the example admin credentials `admin@example.com` / `Admin@12345`. This is only a development credential. Do not reuse it in production; change it before deployment. Do not paste `.env`, database passwords, API keys, or other secrets into AI tools.