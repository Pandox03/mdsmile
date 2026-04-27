<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UsersController extends Controller
{
    public const ROLE_LABELS = [
        'manager' => 'Manager',
        'secretaire' => 'Secrétaire',
        'assistante' => 'Assistante',
        'cadcam' => 'Technicien',
    ];

    public function index(Request $request): View
    {
        $query = User::query()->with('roles')->orderBy('name');

        if ($request->filled('recherche')) {
            $term = $request->get('recherche');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                    ->orWhere('email', 'like', '%' . $term . '%');
            });
        }
        if ($request->filled('role')) {
            $query->role($request->get('role'));
        }

        $users = $query->paginate(15)->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roleLabels' => self::ROLE_LABELS,
        ]);
    }

    public function create(): View
    {
        return view('users.create', [
            'roleLabels' => self::ROLE_LABELS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:manager,secretaire,assistante,cadcam'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);
        ActivityLog::log(ActivityLog::ACTION_CREATED, ActivityLog::SUBJECT_USER, $user->id, 'Utilisateur ' . $user->name . ' créé');

        return redirect()->route('users.index')->with('success', 'Utilisateur créé.');
    }

    public function edit(User $user): View
    {
        $user->load('roles');
        return view('users.edit', [
            'user' => $user,
            'roleLabels' => self::ROLE_LABELS,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', 'in:manager,secretaire,assistante,cadcam'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->syncRoles([$validated['role']]);
        ActivityLog::log(ActivityLog::ACTION_UPDATED, ActivityLog::SUBJECT_USER, $user->id, 'Utilisateur ' . $user->name . ' modifié');

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }
        $name = $user->name;
        $user->delete();
        ActivityLog::log(ActivityLog::ACTION_DELETED, ActivityLog::SUBJECT_USER, null, 'Utilisateur ' . $name . ' supprimé');
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
}
