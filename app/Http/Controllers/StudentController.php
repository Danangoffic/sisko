<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    public function index(): Response
    {
        $students = Student::with(['user', 'schoolClass'])
            ->latest()
            ->paginate(15);

        $classes = SchoolClass::orderBy('name')->get(['id', 'name']);

        return Inertia::render('students/index', [
            'students' => $students,
            'classes' => $classes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'nisn' => ['required', 'string', 'size:10', 'unique:students,nisn'],
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:L,P'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'no_telp_ortu' => ['nullable', 'string', 'max:20'],
            'nama_ortu' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($validated): void {
            $userId = null;

            if (! empty($validated['email'])) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make(Str::password(12)),
                    'role' => Role::Siswa,
                    'email_verified_at' => now(),
                ]);
                $userId = $user->id;

                // Kirim link set-password ke email siswa
                $broker = app('auth.password.broker');
                $broker->sendResetLink(['email' => $user->email]);
            }

            Student::create([
                'school_class_id' => $validated['school_class_id'],
                'user_id' => $userId,
                'nisn' => $validated['nisn'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'no_telp_ortu' => $validated['no_telp_ortu'] ?? null,
                'nama_ortu' => $validated['nama_ortu'] ?? null,
            ]);
        });

        return back()->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'nisn' => ['required', 'string', 'size:10', Rule::unique('students', 'nisn')->ignore($student->id)],
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:L,P'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($student->user_id)],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'no_telp_ortu' => ['nullable', 'string', 'max:20'],
            'nama_ortu' => ['nullable', 'string', 'max:100'],
        ]);

        DB::transaction(function () use ($validated, $student): void {
            if ($student->user) {
                $student->user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? $student->user->email,
                ]);
            } elseif (! empty($validated['email'])) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make(Str::password(12)),
                    'role' => Role::Siswa,
                    'email_verified_at' => now(),
                ]);
                $student->user_id = $user->id;
            }

            $student->update([
                'school_class_id' => $validated['school_class_id'],
                'nisn' => $validated['nisn'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'tempat_lahir' => $validated['tempat_lahir'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'alamat' => $validated['alamat'] ?? null,
                'no_telp_ortu' => $validated['no_telp_ortu'] ?? null,
                'nama_ortu' => $validated['nama_ortu'] ?? null,
            ]);
        });

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        DB::transaction(function () use ($student): void {
            $user = $student->user;
            $student->delete();

            if ($user) {
                $user->delete();
            }
        });

        return back()->with('success', 'Siswa berhasil dihapus.');
    }
}
