<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookLoan>
 */
class BookLoanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'student_id' => Student::factory(),
            'borrowed_at' => now()->subDays(7)->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'returned_at' => null,
            'status' => 'dipinjam',
            'fine' => 0,
        ];
    }
}
