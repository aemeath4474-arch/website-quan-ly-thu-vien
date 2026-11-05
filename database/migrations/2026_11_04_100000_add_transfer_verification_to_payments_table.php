<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('transfer_reference', 60)->nullable()->unique();
            $table->string('verification_status', 20)->default('awaiting')->index();
            $table->string('customer_transaction_code', 100)->nullable();
            $table->timestamp('customer_submitted_at')->nullable();
            $table->text('verification_note')->nullable();
        });
    }
    public function down(): void {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['transfer_reference']);
            $table->dropIndex(['verification_status']);
            $table->dropColumn(['transfer_reference','verification_status','customer_transaction_code','customer_submitted_at','verification_note']);
        });
    }
};
