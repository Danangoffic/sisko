<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Role;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->class = SchoolClass::factory()->create();
    }

    public function test_admin_can_view_students(): void
    {
        Student::factory(3)->create(['school_class_id' => $this->class->id]);

        $response = $this->actingAs($this->admin)->get('/students');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_create_student_without_user_account(): void
    {
        $this->actingAs($this->admin)
            ->post('/students', [
                'school_class_id' => $this->class->id,
                'nisn' => '1234567890',
                'name' => 'Budi Siswa',
                'gender' => 'L',
                'nama_ortu' => 'Pak Budi',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('students', ['nisn' => '1234567890', 'user_id' => null]);
    }

    public function test_admin_can_create_student_with_user_account(): void
    {
        $this->actingAs($this->admin)
            ->post('/students', [
                'school_class_id' => $this->class->id,
                'nisn' => '1234567890',
                'name' => 'Siti Siswa',
                'gender' => 'P',
                'email' => 'siti@sisko.test',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'siti@sisko.test', 'role' => Role::Siswa->value]);
        $this->assertDatabaseHas('students', ['nisn' => '1234567890']);

        // Password tidak boleh literal 'password'
        $user = User::where('email', 'siti@sisko.test')->first();
        $this->assertFalse(Hash::check('password', $user->password));
    }

    public function test_admin_can_update_student(): void
    {
        $student = Student::factory()->create(['school_class_id' => $this->class->id]);

        $this->actingAs($this->admin)
            ->put("/students/{$student->id}", [
                'school_class_id' => $this->class->id,
                'nisn' => $student->nisn,
                'name' => 'Nama Baru',
                'gender' => $student->gender,
                'nama_ortu' => 'Ortu Baru',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('students', ['id' => $student->id, 'name' => 'Nama Baru']);
    }

    public function test_admin_can_add_email_to_existing_student_without_user_and_send_reset_link(): void
    {
        Notification::fake();

        $student = Student::factory()->create([
            'school_class_id' => $this->class->id,
            'user_id' => null,
        ]);

        $this->actingAs($this->admin)
            ->put("/students/{$student->id}", [
                'school_class_id' => $this->class->id,
                'nisn' => $student->nisn,
                'name' => $student->name,
                'gender' => $student->gender,
                'email' => 'siswa-baru@sisko.test',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'siswa-baru@sisko.test',
            'role' => Role::Siswa->value,
        ]);

        $user = User::where('email', 'siswa-baru@sisko.test')->firstOrFail();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_admin_can_delete_student_without_user(): void
    {
        $student = Student::factory()->create(['school_class_id' => $this->class->id, 'user_id' => null]);

        $this->actingAs($this->admin)
            ->delete("/students/{$student->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_admin_can_delete_student_with_user(): void
    {
        $user = User::factory()->create(['role' => Role::Siswa]);
        $student = Student::factory()->create(['school_class_id' => $this->class->id, 'user_id' => $user->id]);

        $this->actingAs($this->admin)
            ->delete("/students/{$student->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_non_admin_cannot_access_students(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/students')->assertStatus(403);
    }

    public function test_student_creation_requires_unique_nisn(): void
    {
        Student::factory()->create(['school_class_id' => $this->class->id, 'nisn' => '1234567890']);

        $this->actingAs($this->admin)
            ->post('/students', [
                'school_class_id' => $this->class->id,
                'nisn' => '1234567890',
                'name' => 'Duplikat',
                'gender' => 'L',
            ])
            ->assertSessionHasErrors('nisn');
    }

    public function test_student_creation_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@sisko.test']);

        $this->actingAs($this->admin)
            ->post('/students', [
                'school_class_id' => $this->class->id,
                'nisn' => '9876543210',
                'name' => 'Test',
                'gender' => 'L',
                'email' => 'existing@sisko.test',
            ])
            ->assertSessionHasErrors('email');
    }
}
