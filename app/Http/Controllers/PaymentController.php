<?php
namespace App\Http\Controllers;
use App\Models\Payment;
use App\Models\BookNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class PaymentController extends Controller
{
    private function ownedPayment(Payment $payment): Payment {
        abort_unless($payment->order()->where('user_id', auth()->id())->exists(), 403);
        return $payment;
    }
    public function show(Payment $payment): View {
        $this->ownedPayment($payment);
        if (!$payment->transfer_reference) {
            DB::transaction(function () use ($payment) {
                $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                if (!$locked->transfer_reference) {
                    $locked->update(['transfer_reference' => 'BHP' . $locked->id . 'O' . $locked->order_id]);
                }
            });
            $payment->refresh();
        }
        $payment->load('order');
        return view('user.payments.show', compact('payment'));
    }
    public function submit(Request $request, Payment $payment): RedirectResponse {
        $this->ownedPayment($payment);
        $data = $request->validate(['transaction_code'=>['required','string','min:4','max:100']]);
        try {
            DB::transaction(function () use ($payment, $data) {
                $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                if ($locked->status !== 'pending') throw new \RuntimeException('Khoản thu đã được xử lý.');
                if ($locked->verification_status === 'submitted') throw new \RuntimeException('Bạn đã gửi yêu cầu xác minh.');
                if (!$locked->bankConfigured()) throw new \RuntimeException('Ngân hàng nhận tiền chưa được cấu hình.');
                $code = strtoupper(trim($data['transaction_code']));
                if (Payment::where('id','!=',$locked->id)->whereRaw('UPPER(customer_transaction_code) = ?',[$code])->whereIn('verification_status',['submitted','verified'])->exists()) throw new \RuntimeException('Mã giao dịch đã được sử dụng trong một yêu cầu thanh toán khác.');
                $locked->update([
                    'payment_method'=>'bank_transfer', 'verification_status'=>'submitted',
                    'customer_transaction_code'=>trim($data['transaction_code']),
                    'customer_submitted_at'=>now(), 'verification_note'=>null,
                ]);
            });
        } catch (\RuntimeException $e) { return back()->with('error',$e->getMessage()); }
        return redirect()->route('my-payments.show', $payment)->with('success','Đã gửi thông tin chuyển khoản. Vui lòng chờ quản trị viên đối soát.');
    }
}
