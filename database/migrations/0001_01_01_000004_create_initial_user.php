<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Bootstraps the deployed app's first user from INITIAL_USER_EMAIL
     * + INITIAL_USER_PASSWORD env vars (injected by the MakerLoft app
     * spec). The first user is flagged as an admin when INITIAL_USER_IS_ADMIN
     * is 'true' - canAccessPanel() on the User model gates Filament
     * /admin on that flag. No-ops when email/password are missing so
     * the migration is safe to run locally without the vars set.
     *
     * The User model's 'password' cast is 'hashed', so assigning
     * plaintext auto-hashes on save. email_verified_at and is_admin
     * are set via direct assignment (not in $fillable) so they bypass
     * mass-assignment protection.
     */
    public function up(): void
    {
        $email = env('INITIAL_USER_EMAIL');
        $password = env('INITIAL_USER_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->fill([
            'name' => 'Admin',
            'password' => $password,
        ]);
        $user->email_verified_at = now();
        // filter_var: DotEnv coerces "true" -> (bool) true on boot, but
        // a cached config passes the raw string through. Accept both.
        $user->is_admin = filter_var(env('INITIAL_USER_IS_ADMIN'), FILTER_VALIDATE_BOOLEAN);
        $user->save();
    }

    public function down(): void {}
};
