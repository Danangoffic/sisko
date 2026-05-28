<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassPromotion;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassPromotionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private AcademicYear $academicYear;

    private SchoolClass $fromClass;

    private SchoolClass $toClass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->academicYear = AcademicYear::factory()->create();
        $this->fromClass = SchoolClass::factory()->create();
        $this->toClass = SchoolClass::factory()->create();
    }

    public function test_admin_can_view_promotions(): void
    {
        $response = $this->actingAs($this->admin)->get('/promotions');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_store_individual_promotion(): void
    {
        $student = Student::factory()->create(['school_class_id' => $this->fromClass->id]);

        $this->actingAs($this->admin)
            ->post('/promotions', [
                'student_id' => $student->id,
                'from_class_id' => $this->fromClass->id,
                'to_class_id' => $this->toClass->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'naik',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('class_promotions', [
            'student_id' => $student->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'naik',
        ]);
    }

    public function test_individual_promotion_upserts_on_duplicate(): void
    {
        $student = Student::factory()->create(['school_class_id' => $this->fromClass->id]);

        $this->actingAs($this->admin)->post('/promotions', [
            'student_id' => $student->id,
            'from_class_id' => $this->fromClass->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'naik',
        ]);

        $this->actingAs($this->admin)->post('/promotions', [
            'student_id' => $student->id,
            'from_class_id' => $this->fromClass->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'tinggal',
        ])->assertRedirect();

        $this->assertDatabaseCount('class_promotions', 1);
        $this->assertDatabaseHas('class_promotions', ['student_id' => $student->id, 'status' => 'tinggal']);
    }

    public function test_admin_can_promote_batch(): void
    {
        Student::factory(3)->create(['school_class_id' => $this->fromClass->id]);

        $this->actingAs($this->admin)
            ->post('/promotions/batch', [
                'from_class_id' => $this->fromClass->id,
                'to_class_id' => $this->toClass->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'naik',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('class_promotions', 3);
    }

    public function test_batch_skips_empty_class(): void
    {
        $emptyClass = SchoolClass::factory()->create();

        $this->actingAs($this->admin)
            ->post('/promotions/batch', [
                'from_class_id' => $emptyClass->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'naik',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('class_promotions', 0);
    }

    public function test_admin_can_update_promotion(): void
    {
        $promotion = ClassPromotion::factory()->create([
            'from_class_id' => $this->fromClass->id,
            'to_class_id' => $this->toClass->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'naik',
        ]);

        $this->actingAs($this->admin)
            ->put("/promotions/{$promotion->id}", [
                'to_class_id' => $this->toClass->id,
                'status' => 'tinggal',
                'notes' => 'Nilai tidak memenuhi syarat',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('class_promotions', ['id' => $promotion->id, 'status' => 'tinggal']);
    }

    public function test_admin_can_delete_promotion(): void
    {
        $promotion = ClassPromotion::factory()->create([
            'from_class_id' => $this->fromClass->id,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->actingAs($this->admin)->delete("/promotions/{$promotion->id}")->assertRedirect();

        $this->assertDatabaseMissing('class_promotions', ['id' => $promotion->id]);
    }

    public function test_non_admin_cannot_access_promotions(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/promotions')->assertStatus(403);
    }
}
