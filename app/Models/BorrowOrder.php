<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class BorrowOrder extends Model {
    protected $fillable = ['user_id','order_code','transaction_type','order_date','status','total_amount','note','approved_at','returned_at'];
    protected function casts(): array { return ['order_date'=>'datetime','approved_at'=>'datetime','returned_at'=>'datetime','total_amount'=>'decimal:2']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(BorrowOrderItem::class, 'order_id'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class, 'order_id'); }
}
