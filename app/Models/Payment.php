<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model {
    protected $fillable = ['order_id','amount','payment_type','payment_method','status','transaction_code','paid_at','transfer_reference','verification_status','customer_transaction_code','customer_submitted_at','verification_note'];
    protected function casts(): array { return ['amount'=>'decimal:2','paid_at'=>'datetime','customer_submitted_at'=>'datetime']; }
    public function transferContent(): string { return $this->transfer_reference ?: 'BH-P' . $this->id; }
    public function bankConfigured(): bool {
        return filled(config('bookhub_payment.bank_bin')) && filled(config('bookhub_payment.bank_account')) && filled(config('bookhub_payment.bank_name'));
    }
    public function qrUrl(): ?string {
        if (!$this->bankConfigured() || (float) $this->amount <= 0 || (float) $this->amount != floor((float) $this->amount)) return null;
        $bin = preg_replace('/[^0-9]/', '', (string) config('bookhub_payment.bank_bin'));
        $account = preg_replace('/[^0-9A-Za-z]/', '', (string) config('bookhub_payment.bank_account'));
        if (!$bin || !$account) return null;
        return 'https://img.vietqr.io/image/' . rawurlencode($bin) . '-' . rawurlencode($account)
            . '-compact2.png?' . http_build_query([
                'amount' => (int) $this->amount,
                'addInfo' => $this->transferContent(),
                'accountName' => config('bookhub_payment.bank_name'),
            ], '', '&', PHP_QUERY_RFC3986);
    }
    public function order(): BelongsTo { return $this->belongsTo(BorrowOrder::class, 'order_id'); }
}
