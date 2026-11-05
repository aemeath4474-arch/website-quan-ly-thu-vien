<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class BorrowOrderItem extends Model {
    protected $fillable = ['order_id','book_id','quantity','unit_price','borrow_date','due_date','returned_date','status','late_fee'];
    protected function casts(): array { return ['borrow_date'=>'date','due_date'=>'date','returned_date'=>'date','unit_price'=>'decimal:2','late_fee'=>'decimal:2']; }
    public function order(): BelongsTo { return $this->belongsTo(BorrowOrder::class, 'order_id'); }
    public function book(): BelongsTo { return $this->belongsTo(Book::class); }
}
