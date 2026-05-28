<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->enum('target_role', ['all', 'guru', 'siswa'])->default('all');
            $table->boolean('is_pinned')->default(false);
            $table->datetime('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
