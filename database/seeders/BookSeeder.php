<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            ['title' => 'Pengantar Pemrograman', 'author' => 'Budi Santoso', 'isbn' => '978-602-01-0001-0', 'publisher' => 'Edukita', 'year' => 2020, 'category' => 'Teknologi', 'stock' => 5],
            ['title' => 'Matematika Dasar', 'author' => 'Siti Aminah', 'isbn' => '978-602-01-0002-7', 'publisher' => 'Pelita', 'year' => 2018, 'category' => 'Matematika', 'stock' => 8],
            ['title' => 'Sejarah Indonesia', 'author' => 'Andi Wijaya', 'isbn' => '978-602-01-0003-4', 'publisher' => 'Nusantara', 'year' => 2015, 'category' => 'Sejarah', 'stock' => 3],
            ['title' => 'Bahasa Inggris Praktis', 'author' => 'Lisa Marlina', 'isbn' => '978-602-01-0004-1', 'publisher' => 'Global', 'year' => 2021, 'category' => 'Bahasa', 'stock' => 10],
            ['title' => 'Fisika untuk SMA', 'author' => 'Rudi Hartono', 'isbn' => '978-602-01-0005-8', 'publisher' => 'SainsPress', 'year' => 2019, 'category' => 'Fisika', 'stock' => 6],
        ];

        foreach ($books as $data) {
            Book::firstOrCreate(['isbn' => $data['isbn']], $data);
        }
    }
}
