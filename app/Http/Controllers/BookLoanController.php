<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BookLoanController extends Controller
{
    private const FINE_PER_DAY = 1000;

    public function index(): Response
    {
        $loans = BookLoan::with(['book', 'student'])
            ->latest('borrowed_at')
            ->paginate(20);

        return Inertia::render('book-loans/index', [
            'loans' => $loans,
            'books' => Book::where('stock', '>', 0)->orderBy('title')->get(['id', 'title', 'stock']),
            'students' => Student::orderBy('name')->get(['id', 'name', 'nisn']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'student_id' => ['required', 'exists:students,id'],
            'borrowed_at' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after:borrowed_at'],
        ]);

        $book = Book::findOrFail($validated['book_id']);

        if ($book->stock < 1) {
            return back()->with('error', 'Stok buku habis.');
        }

        DB::transaction(function () use ($validated, $book): void {
            BookLoan::create([
                ...$validated,
                'status' => 'dipinjam',
                'fine' => 0,
            ]);

            $book->decrement('stock');
        });

        return back()->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function returnBook(BookLoan $bookLoan): RedirectResponse
    {
        if ($bookLoan->status === 'dikembalikan') {
            return back()->with('error', 'Buku sudah dikembalikan.');
        }

        $returnDate = now();
        $fine = 0;

        if ($returnDate->greaterThan($bookLoan->due_date)) {
            $daysLate = (int) $bookLoan->due_date->diffInDays($returnDate);
            $fine = $daysLate * self::FINE_PER_DAY;
        }

        DB::transaction(function () use ($bookLoan, $returnDate, $fine): void {
            $bookLoan->update([
                'returned_at' => $returnDate->format('Y-m-d'),
                'status' => 'dikembalikan',
                'fine' => $fine,
            ]);

            $bookLoan->book->increment('stock');
        });

        $message = 'Buku berhasil dikembalikan.';
        if ($fine > 0) {
            $message .= ' Denda: Rp '.number_format($fine, 0, ',', '.');
        }

        return back()->with('success', $message);
    }

    public function destroy(BookLoan $bookLoan): RedirectResponse
    {
        if ($bookLoan->status === 'dipinjam') {
            $bookLoan->book->increment('stock');
        }

        $bookLoan->delete();

        return back()->with('success', 'Data peminjaman berhasil dihapus.');
    }
}
