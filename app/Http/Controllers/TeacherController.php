<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TeacherController extends Controller
{
    public function index(): Response
    {
        $teachers = Teacher::with('user')
            ->latest()
            ->paginate(15);

        return Inertia::render('teachers/index', [
            'teachers' => $teachers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'nip' => ['nullable', 'string', 'max:20', 'unique:teachers,nip'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make('password'),
                'role' => Role::Guru,
                'email_verified_at' => now(),
            ]);

            Teacher::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        });

        return back()->with('success', 'Guru berhasil ditambahkan.');
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'nip' => ['nullable', 'string', 'max:20', Rule::unique('teachers', 'nip')->ignore($teacher->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $teacher): void {
            $teacher->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $teacher->update([
                'nip' => $validated['nip'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
        });

        return back()->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $teacher->user->delete(); // cascades to teacher

        return back()->with('success', 'Guru berhasil dihapus.');
    }
}
