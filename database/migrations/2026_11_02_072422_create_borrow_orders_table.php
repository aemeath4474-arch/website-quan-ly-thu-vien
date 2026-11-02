<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('borrow_orders', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
            ->constrained('users')
            ->restrictOnDelete();

        $table->string('order_code', 30)->unique();

        $table->enum('transaction_type', ['borrow', 'rent']);

        $table->dateTime('order_date');

        $table->enum('status', [
            'pending',
            'approved',
            'rejected',
            'borrowing',
            'returned',
            'overdue',
            'cancelled'
        ])->default('pending');

        $table->decimal('total_amount', 12, 2)->default(0);
        $table->text('note')->nullable();
        $table->dateTime('approved_at')->nullable();
        $table->dateTime('returned_at')->nullable();

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('borrow_orders');
}
};
