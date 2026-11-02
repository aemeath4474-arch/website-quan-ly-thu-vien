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
    Schema::create('books', function (Blueprint $table) {
        $table->id();

        $table->foreignId('category_id')
            ->constrained('categories')
            ->restrictOnDelete();

        $table->string('title', 255);
        $table->string('isbn', 20)->unique();
        $table->string('publisher', 150)->nullable();
        $table->unsignedSmallInteger('publication_year')->nullable();
        $table->text('description')->nullable();
        $table->string('cover_image')->nullable();
        $table->decimal('price', 12, 2)->default(0);
        $table->decimal('rental_price', 12, 2)->default(0);
        $table->unsignedInteger('total_copies')->default(0);
        $table->unsignedInteger('available_copies')->default(0);
        $table->enum('status', ['active', 'hidden'])->default('active');

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('books');
}
};
