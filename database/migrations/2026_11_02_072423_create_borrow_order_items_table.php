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
    Schema::create('borrow_order_items', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')
            ->constrained('borrow_orders')
            ->cascadeOnDelete();

        $table->foreignId('book_id')
            ->constrained('books')
            ->restrictOnDelete();

        $table->unsignedInteger('quantity');
        $table->decimal('unit_price', 12, 2)->default(0);

        $table->date('borrow_date');
        $table->date('due_date');
        $table->date('returned_date')->nullable();

        $table->string('status', 30)->default('borrowing');

        $table->decimal('late_fee', 12, 2)->default(0);

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('borrow_order_items');
}
};
