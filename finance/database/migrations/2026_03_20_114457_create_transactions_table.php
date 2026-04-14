<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('operation_date');
            $table->time('operation_time')->nullable();
            $table->string('description', 255)->nullable();
            $table->enum('status', ['accepted', 'rejected', 'in_processing'])->default('in_processing');
            $table->foreignId('transfer_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'operation_date']);
            $table->index('account_id');
            $table->index('category_id');
            $table->index('operation_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
