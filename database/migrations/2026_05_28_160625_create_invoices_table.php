<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number', 30)->unique();
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->datetime('paid_at')->nullable();
            $table->string('month', 7)->nullable(); // e.g. 2026-01
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
