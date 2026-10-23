<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const ASSIGNABLE_ROLES = ['Admin', 'Customer'];

    public function index(): View
    {
        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->paginate(20);

        return view('pages.admin.roles.index', [
            'users' => $users,
            'roles' => Role::query()
                ->whereIn('name', self::ASSIGNABLE_ROLES)
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->hasRole('Super Admin') || $request->user()->is($user), 403);

        $validated = $request->validate([
            'role' => ['required', 'string', Rule::in(self::ASSIGNABLE_ROLES)],
        ]);

        $user->syncRoles($validated['role']);

        return to_route('admin.roles.index')->with('status', 'Role pengguna berhasil diperbarui.');
    }
}
