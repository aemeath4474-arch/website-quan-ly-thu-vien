<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\BankStatementTransaction;
use App\Models\BookNotification;
use App\Services\BankTransferMatcher;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class PaymentController extends Controller {
 public function index(Request $request, BankTransferMatcher $matcher):View {
  $payments=Payment::with('order.user')
    ->when($request->filled('status'),fn($q)=>$q->where('status',$request->status))
    ->when($request->filled('type'),fn($q)=>$q->where('payment_type',$request->type))
    ->when($request->filled('verification'),fn($q)=>$q->where('verification_status',$request->verification))
    ->orderByRaw("CASE WHEN status = 'pending' AND verification_status = 'submitted' THEN 0 ELSE 1 END")
    ->latest()->paginate(20)->withQueryString();
  $transactionIds=$payments->getCollection()->pluck('customer_transaction_code')->filter()->map(fn($x)=>strtoupper(trim($x)))->all();
  $bankRows=BankStatementTransaction::whereIn('transaction_id',$transactionIds)->get()->keyBy('transaction_id');
  $matchErrors=[];$matchIds=[];
  foreach($payments as $payment){
   if($payment->status==='pending' && $payment->verification_status==='submitted'){
    $bank=$bankRows->get(strtoupper(trim((string)$payment->customer_transaction_code)));
    $matchErrors[$payment->id]=$bank?$matcher->match($payment,$bank):['Chưa tìm thấy giao dịch trong sao kê đã nhập.'];
    if($bank)$matchIds[$payment->id]=$bank->id;
   }
  }
  $pendingAmount=Payment::where('status','pending')->sum('amount');
  $receivedAmount=Payment::where('status','completed')->sum('amount');
  $awaitingVerification=Payment::where('status','pending')->where('verification_status','submitted')->count();
  return view('admin.payments.index',compact('payments','pendingAmount','receivedAmount','awaitingVerification','matchErrors','matchIds'));
 }
 public function complete(Request $request, Payment $payment, BankTransferMatcher $matcher):RedirectResponse {
  $data=$request->validate([
   'bank_transaction_id'=>['required','integer','exists:bank_statement_transactions,id'],
  ]);
  try {
   DB::transaction(function()use($payment,$data,$matcher,$request){
    $locked=Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
    $bank=BankStatementTransaction::whereKey($data['bank_transaction_id'])->lockForUpdate()->firstOrFail();
    $errors=$matcher->match($locked,$bank);
    if($errors) throw new \RuntimeException(implode(' ',$errors));
    $bank->update(['matched_payment_id'=>$locked->id]);
    $locked->update([
      'status'=>'completed','payment_method'=>'bank_transfer','verification_status'=>'verified',
      'transaction_code'=>$bank->transaction_id,'verification_note'=>null,'paid_at'=>$bank->transaction_at,
    ]);
    BookNotification::create([
     'user_id'=>$locked->order->user_id,'title'=>'Thanh toán chuyển khoản đã được xác nhận',
     'message'=>'Đã đối soát khoản thanh toán cho đơn '.$locked->order->order_code.' ('.number_format((float)$locked->amount,0,',','.').' đ).',
     'type'=>'order','created_at'=>now(),
    ]);
   });
  }catch(\RuntimeException $e){return back()->with('error',$e->getMessage());}
  catch(\Throwable $e){report($e);return back()->with('error','Không thể duyệt giao dịch. Kiểm tra xem mã sao kê đã được đối soát ở khoản khác chưa.');}
  return back()->with('success','Đã xác minh đủ điều kiện đối soát và ghi nhận thanh toán.');
 }
 public function reject(Request $request, Payment $payment):RedirectResponse {
  $data=$request->validate(['reason'=>['required','string','min:5','max:500']]);
  try{DB::transaction(function()use($payment,$data){
   $locked=Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
   if($locked->status!=='pending'||$locked->verification_status!=='submitted')throw new \RuntimeException('Yêu cầu xác minh không còn hiệu lực.');
   $locked->update(['verification_status'=>'rejected','verification_note'=>$data['reason']]);
   BookNotification::create(['user_id'=>$locked->order->user_id,'title'=>'Cần kiểm tra lại thông tin chuyển khoản',
    'message'=>'Giao dịch cho đơn '.$locked->order->order_code.' chưa được xác minh: '.$data['reason'],
    'type'=>'order','created_at'=>now()]);
  });}catch(\RuntimeException $e){return back()->with('error',$e->getMessage());}
  return back()->with('success','Đã yêu cầu khách hàng cung cấp lại thông tin.');
 }
}
