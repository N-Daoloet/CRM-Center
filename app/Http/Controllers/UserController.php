<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->latest('id')
            ->get();

        $nextEmployeeCode = User::generateNextEmployeeCode();

        return view('users.index', [
            'users' => $users,
            'nextEmployeeCode' => $nextEmployeeCode,
        ]);
    }

    public function create(): View
    {
        $supervisors = User::query()->where('role', 'supervisor')->orderBy('name')->get();

        return view('users.create', compact('supervisors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(['admin', 'supervisor', 'owner', 'sale'])],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'] ?? null,
            'role' => $validated['role'],
            'password' => $validated['password'],
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');
    }
}
