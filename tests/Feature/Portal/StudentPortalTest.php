<?php

namespace Tests\Feature\Portal;

use App\Models\Grade;
use App\Models\Invoice;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $siswaUser;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->siswaUser = User::factory()->create(['role' => Role::Siswa]);
        $class = SchoolClass::factory()->create();
        $this->student = Student::factory()->create([
            'user_id' => $this->siswaUser->id,
            'school_class_id' => $class->id,
        ]);
    }

    public function test_siswa_can_view_portal_schedule(): void
    {
        $response = $this->actingAs($this->siswaUser)->get('/portal/schedule');

        $response->assertOk();
    }

    public function test_siswa_can_view_portal_attendances(): void
    {
        $response = $this->actingAs($this->siswaUser)->get('/portal/attendances');

        $response->assertOk();
    }

    public function test_siswa_can_view_portal_grades(): void
    {
        $response = $this->actingAs($this->siswaUser)->get('/portal/grades');

        $response->assertOk();
    }

    public function test_siswa_can_view_portal_report_cards(): void
    {
        $response = $this->actingAs($this->siswaUser)->get('/portal/report-cards');

        $response->assertOk();
    }

    public function test_siswa_can_view_portal_invoices(): void
    {
        $response = $this->actingAs($this->siswaUser)->get('/portal/invoices');

        $response->assertOk();
    }

    public function test_siswa_only_sees_own_grades(): void
    {
        $semester = Semester::factory()->create();
        $subject = Subject::factory()->create();
        $teacher = Teacher::factory()->create();

        // Nilai milik siswa ini
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'semester_id' => $semester->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'score' => 90,
        ]);

        // Nilai milik siswa lain
        $otherStudent = Student::factory()->create();
        Grade::factory()->create([
            'student_id' => $otherStudent->id,
            'semester_id' => $semester->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'score' => 70,
        ]);

        $response = $this->actingAs($this->siswaUser)->get('/portal/grades');

        $response->assertOk();
        // Hanya 1 nilai yang dikembalikan (milik siswa ini)
        $response->assertInertia(fn ($page) => $page
            ->component('portal/grades')
            ->where('grades.total', 1)
        );
    }

    public function test_siswa_only_sees_own_invoices(): void
    {
        $invoice = Invoice::factory()->create(['student_id' => $this->student->id]);
        $otherInvoice = Invoice::factory()->create(['student_id' => Student::factory()->create()->id]);

        $response = $this->actingAs($this->siswaUser)->get('/portal/invoices');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('portal/invoices')
            ->where('invoices.total', 1)
        );
    }

    public function test_siswa_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->siswaUser)->get('/students')->assertStatus(403);
        $this->actingAs($this->siswaUser)->get('/teachers')->assertStatus(403);
        $this->actingAs($this->siswaUser)->get('/grades')->assertStatus(403);
    }

    public function test_admin_cannot_access_portal_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/portal/schedule')->assertStatus(403);
    }

    public function test_guru_cannot_access_portal_routes(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/portal/grades')->assertStatus(403);
    }

    public function test_guest_is_redirected_from_portal(): void
    {
        $this->get('/portal/schedule')->assertRedirect(route('login'));
    }
}
