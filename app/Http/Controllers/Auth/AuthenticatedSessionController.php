<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('username');

        // Rate limit by IP + username
        $key = 'login:' . $request->ip() . ':' . strtolower($login);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'username' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        // Find user by username or email
        $user = User::where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (!$user || !Auth::attempt(['email' => $user->email, 'password' => $request->input('password')])) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'username' => 'Invalid username or password.',
            ]);
        }

        RateLimiter::clear($key);

        $request->session()->regenerate();

        // Update last login
        $user->update(['last_login_at' => now()]);

        $home = route($user->dashboardRoute());
        $intended = $request->session()->pull('url.intended');

        return redirect()
            ->to($intended && $this->intendedIsAllowed($user, $intended) ? $intended : $home)
            ->with('success', 'Welcome back, ' . $user->first_name . '!');
    }

    /**
     * Only honour a pre-login intended URL when the role can actually open it.
     */
    private function intendedIsAllowed(User $user, string $intended): bool
    {
        $path = parse_url($intended, PHP_URL_PATH) ?: '/';

        if ($path === '/' || $path === '') {
            return false;
        }

        return match ($user->role) {
            UserRole::Admin => str_starts_with($path, '/admin')
                || str_starts_with($path, '/billing')
                || str_starts_with($path, '/field')
                || str_starts_with($path, '/custodian'),
            UserRole::BillingOfficer => str_starts_with($path, '/billing')
                || str_starts_with($path, '/admin/clients'),
            UserRole::FieldTechnician => str_starts_with($path, '/field'),
            UserRole::ChemicalCustodian => str_starts_with($path, '/custodian'),
            default => false,
        };
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
