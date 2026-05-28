<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_books(): void
    {
        $response = $this->actingAs($this->admin)->get('/books');

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_admin_can_create_book(): void
    {
        $this->actingAs($this->admin)
            ->post('/books', [
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'isbn' => '9789793062792',
                'stock' => 5,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('books', ['title' => 'Laskar Pelangi', 'stock' => 5]);
    }

    public function test_admin_can_update_book(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($this->admin)
            ->put("/books/{$book->id}", [
                'title' => 'Updated Title',
                'author' => $book->author,
                'stock' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Title', 'stock' => 10]);
    }

    public function test_admin_can_delete_book(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($this->admin)->delete("/books/{$book->id}")->assertRedirect();

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_non_admin_cannot_access_books(): void
    {
        $guru = User::factory()->guru()->create();

        $this->actingAs($guru)->get('/books')->assertStatus(403);
    }
}
