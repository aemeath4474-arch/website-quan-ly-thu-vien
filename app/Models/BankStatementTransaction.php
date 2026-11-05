<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BankStatementTransaction extends Model {
 protected $fillable=['transaction_id','bank_bin','account_number','amount','description','transaction_at','imported_by','matched_payment_id'];
 protected function casts():array{return ['amount'=>'decimal:2','transaction_at'=>'datetime'];}
 public function payment():BelongsTo{return $this->belongsTo(Payment::class,'matched_payment_id');}
}
