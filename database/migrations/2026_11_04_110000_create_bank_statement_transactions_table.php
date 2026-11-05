<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('bank_statement_transactions',function(Blueprint $table){
   $table->id();
   $table->string('transaction_id',100)->unique();
   $table->string('bank_bin',12);
   $table->string('account_number',50);
   $table->decimal('amount',12,2);
   $table->string('description',500);
   $table->dateTime('transaction_at');
   $table->foreignId('imported_by')->constrained('users')->restrictOnDelete();
   $table->foreignId('matched_payment_id')->nullable()->unique()->constrained('payments')->nullOnDelete();
   $table->timestamps();
  });
 }
 public function down():void {Schema::dropIfExists('bank_statement_transactions');}
};
