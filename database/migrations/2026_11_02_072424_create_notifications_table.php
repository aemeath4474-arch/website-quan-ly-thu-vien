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
    Schema::create('notifications', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->foreignId('order_item_id')
            ->nullable()
            ->constrained('borrow_order_items')
            ->nullOnDelete();

        $table->string('title', 200);
        $table->text('message');

        $table->enum('type', [
            'due_soon',
            'overdue',
            'order',
            'system'
        ]);

        $table->dateTime('read_at')->nullable();

        $table->timestamp('created_at')->useCurrent();
    });
}

public function down(): void
{
    Schema::dropIfExists('notifications');
}
};
