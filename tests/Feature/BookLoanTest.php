<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookLoanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Book $book;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->book = Book::factory()->create(['stock' => 3]);
        $this->student = Student::factory()->create();
    }

    public function test_admin_can_view_book_loans(): void
    {
        $response = $this->actingAs($this->admin)->get('/book-loans');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_create_loan(): void
    {
        $this->actingAs($this->admin)
            ->post('/book-loans', [
                'book_id' => $this->book->id,
                'student_id' => $this->student->id,
                'borrowed_at' => '2026-05-28',
                'due_date' => '2026-06-04',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('book_loans', ['book_id' => $this->book->id, 'status' => 'dipinjam']);
        $this->assertDatabaseHas('books', ['id' => $this->book->id, 'stock' => 2]);
    }

    public function test_loan_fails_when_stock_zero(): void
    {
        $this->book->update(['stock' => 0]);

        $this->actingAs($this->admin)
            ->post('/book-loans', [
                'book_id' => $this->book->id,
                'student_id' => $this->student->id,
                'borrowed_at' => '2026-05-28',
                'due_date' => '2026-06-04',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('book_loans', 0);
    }

    public function test_admin_can_return_book(): void
    {
        $loan = BookLoan::factory()->create([
            'book_id' => $this->book->id,
            'student_id' => $this->student->id,
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $stockBefore = $this->book->fresh()->stock;

        $this->actingAs($this->admin)
            ->post("/book-loans/{$loan->id}/return")
            ->assertRedirect();

        $this->assertDatabaseHas('book_loans', ['id' => $loan->id, 'status' => 'dikembalikan']);
        $this->assertEquals($stockBefore + 1, $this->book->fresh()->stock);
    }

    public function test_late_return_calculates_fine(): void
    {
        $loan = BookLoan::factory()->create([
            'book_id' => $this->book->id,
            'student_id' => $this->student->id,
            'borrowed_at' => now()->subDays(14)->format('Y-m-d'),
            'due_date' => now()->subDays(7)->format('Y-m-d'),
        ]);

        $this->actingAs($this->admin)
            ->post("/book-loans/{$loan->id}/return")
            ->assertRedirect();

        $loan->refresh();
        $this->assertEquals('dikembalikan', $loan->status);
        $this->assertGreaterThan(0, (float) $loan->fine);
    }

    public function test_admin_can_delete_loan(): void
    {
        $loan = BookLoan::factory()->create([
            'book_id' => $this->book->id,
            'student_id' => $this->student->id,
        ]);

        $stockBefore = $this->book->fresh()->stock;

        $this->actingAs($this->admin)->delete("/book-loans/{$loan->id}")->assertRedirect();

        $this->assertDatabaseMissing('book_loans', ['id' => $loan->id]);
        // Stock restored since loan was active
        $this->assertEquals($stockBefore + 1, $this->book->fresh()->stock);
    }

    public function test_non_admin_cannot_access_book_loans(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/book-loans')->assertStatus(403);
    }
}
