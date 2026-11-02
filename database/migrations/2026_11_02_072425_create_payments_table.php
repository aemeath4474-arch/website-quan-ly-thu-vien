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
    Schema::create('payments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')
            ->constrained('borrow_orders')
            ->restrictOnDelete();

        $table->decimal('amount', 12, 2);

        $table->enum('payment_type', [
            'rental',
            'late_fee',
            'other'
        ]);

        $table->enum('payment_method', [
            'cash',
            'bank_transfer',
            'online'
        ]);

        $table->enum('status', [
            'pending',
            'completed',
            'failed',
            'refunded'
        ])->default('pending');

        $table->string('transaction_code', 100)->nullable();
        $table->dateTime('paid_at')->nullable();

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('payments');
}
};
