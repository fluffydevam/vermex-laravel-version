<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-users');

        $search = trim((string) $request->input('search', ''));
        $role = (string) $request->input('role', '');
        $status = (string) $request->input('status', '');

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role !== '', fn ($q) => $q->where('role', $role))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', UserStatus::Active->value)->count(),
            'admins' => User::where('role', UserRole::Admin->value)->count(),
            'technicians' => User::where('role', UserRole::FieldTechnician->value)->count(),
        ];

        return view('admin.users.index', [
            'users' => $users,
            'stats' => $stats,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'roles' => UserRole::cases(),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function create()
    {
        Gate::authorize('manage-users');

        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        Gate::authorize('manage-users');

        $user = User::create($request->validated());

        return redirect()
            ->route('admin.users')
            ->with('success', "User '{$user->full_name}' created.");
    }

    public function edit(User $user)
    {
        Gate::authorize('manage-users');

        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        Gate::authorize('manage-users');

        $data = $request->validated();

        if ($user->id === $request->user()->id && $data['status'] === UserStatus::Disabled->value) {
            throw ValidationException::withMessages([
                'status' => 'You cannot disable your own account.',
            ]);
        }

        $remainsActiveAdmin = $data['role'] === UserRole::Admin->value
            && $data['status'] === UserStatus::Active->value;

        if ($user->role === UserRole::Admin && ! $remainsActiveAdmin && $this->activeAdminCount() <= 1) {
            throw ValidationException::withMessages([
                'role' => 'The last active administrator cannot be demoted or disabled.',
            ]);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users')
            ->with('success', "User '{$user->full_name}' updated.");
    }

    public function toggleStatus(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'You cannot change the status of your own account.');
        }

        $next = $user->status === UserStatus::Active ? UserStatus::Disabled : UserStatus::Active;

        if ($next === UserStatus::Disabled && $user->role === UserRole::Admin && $this->activeAdminCount() <= 1) {
            return back()->with('error', 'The last active administrator cannot be disabled.');
        }

        $user->update(['status' => $next]);

        return back()->with('success', "User '{$user->full_name}' is now {$next->value}.");
    }

    public function resetPassword(Request $request, User $user)
    {
        Gate::authorize('manage-users');

        $data = $request->validate([
            'new_password' => 'required|string|min:8',
        ]);

        $user->update(['password' => $data['new_password']]);

        return back()->with('success', "Password reset for '{$user->full_name}'.");
    }

    private function activeAdminCount(): int
    {
        return User::query()
            ->where('role', UserRole::Admin->value)
            ->where('status', UserStatus::Active->value)
            ->count();
    }
}
