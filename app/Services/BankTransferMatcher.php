<?php
namespace App\Services;
use App\Models\Payment;
use App\Models\BankStatementTransaction;
class BankTransferMatcher {
 public function normalize(string $value):string {return strtoupper(trim($value));}
 public function account(string $value):string{return preg_replace('/[^0-9A-Za-z]/','',$value);}
 public function match(Payment $payment, BankStatementTransaction $transaction):array {
  $errors=[];
  if ($payment->status !== 'pending' || $payment->verification_status !== 'submitted' || $payment->payment_method !== 'bank_transfer') $errors[]='Yêu cầu chuyển khoản không ở trạng thái chờ đối soát.';
  if ($transaction->matched_payment_id !== null && (int)$transaction->matched_payment_id !== (int)$payment->id) $errors[]='Giao dịch ngân hàng đã được sử dụng cho khoản thanh toán khác.';
  if (!$payment->customer_transaction_code || $this->normalize($payment->customer_transaction_code)!==$this->normalize($transaction->transaction_id)) $errors[]='Mã giao dịch khách khai báo không khớp sao kê.';
  if ($this->account($transaction->bank_bin) !== $this->account((string)config('bookhub_payment.bank_bin'))) $errors[]='Mã BIN ngân hàng nhận không khớp.';
  if ($this->account($transaction->account_number) !== $this->account((string)config('bookhub_payment.bank_account'))) $errors[]='Số tài khoản nhận không khớp.';
  // Work in integer cents to avoid float rounding errors.
  if (number_format((float)$transaction->amount,2,'.','') !== number_format((float)$payment->amount,2,'.','')) $errors[]='Số tiền nhận không khớp số tiền phải thu.';
  $reference=$this->normalize($payment->transferContent());
  $description=$this->normalize($transaction->description);
  if (!$payment->transfer_reference || !preg_match('/(?<![A-Z0-9])'.preg_quote($reference,'/').'(?![A-Z0-9])/u',$description)) $errors[]='Nội dung chuyển khoản không chứa đúng mã tham chiếu riêng.';
  return $errors;
 }
}
