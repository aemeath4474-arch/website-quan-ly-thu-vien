<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\BankStatementTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
class BankStatementController extends Controller {
 public function index(): View {
  $transactions=BankStatementTransaction::with('payment')->latest('transaction_at')->paginate(30);
  return view('admin.payments.statements',compact('transactions'));
 }
 public function upload(Request $request): RedirectResponse {
  $request->validate(['statement'=>['required','file','mimes:csv,txt','max:2048']]);
  $file=$request->file('statement');
  $handle=fopen($file->getRealPath(),'rb');
  if ($handle===false) return back()->with('error','Không mở được tệp sao kê.');
  $header=fgetcsv($handle,0,',','"','\\');
  $expected=['transaction_id','bank_bin','account_number','amount','description','transaction_at'];
  if (!$header || array_map(fn($v)=>trim((string)$v),$header)!==$expected){fclose($handle);return back()->with('error','CSV cần đúng 6 cột: '.implode(', ',$expected));}
  $rows=[];$errors=[];$line=1;
  while (($columns=fgetcsv($handle,0,',','"','\\'))!==false){
   $line++;
   if (count($columns)===1 && trim((string)$columns[0])==='') continue;
   if ($line>501){$errors[]='Mỗi lượt tối đa 500 giao dịch.';break;}
   if(count($columns)!==6){$errors[]="Dòng $line: sai số cột.";continue;}
   $row=array_combine($expected,array_map('trim',$columns));
   $v=Validator::make($row,[
    'transaction_id'=>['required','string','max:100','regex:/^[A-Za-z0-9._-]+$/'],
    'bank_bin'=>['required','regex:/^[0-9]{6}$/'],
    'account_number'=>['required','regex:/^[0-9A-Za-z]{4,50}$/'],
    'amount'=>['required','regex:/^[0-9]{1,10}(\.[0-9]{1,2})?$/'],
    'description'=>['required','string','max:500'],
    'transaction_at'=>['required','date_format:Y-m-d H:i:s'],
   ]);
   if($v->fails()){$errors[]="Dòng $line: ".$v->errors()->first();continue;}
   if((float)$row['amount']<=0){$errors[]="Dòng $line: chỉ chấp nhận giao dịch tiền vào dương.";continue;}
   $row['transaction_id']=strtoupper($row['transaction_id']);
   if(isset($rows[$row['transaction_id']])){$errors[]="Dòng $line: mã giao dịch lặp trong tệp.";continue;}
   $rows[$row['transaction_id']]=$row;
  }
  fclose($handle);
  if($errors) return back()->with('error','Không nhập tệp: '.implode(' | ',array_slice($errors,0,8)));
  if(!$rows) return back()->with('error','Tệp không có giao dịch hợp lệ.');
  $count=0;
  try {
   DB::transaction(function ()use($rows,&$count,$request){
    foreach($rows as $row){
     $existing=BankStatementTransaction::where('transaction_id',$row['transaction_id'])->lockForUpdate()->first();
     if($existing){
      // Existing transaction cannot silently be overwritten or replaced.
      foreach(['bank_bin','account_number','description','transaction_at'] as $column){
       $old=$column==='transaction_at'?$existing->transaction_at->format('Y-m-d H:i:s'):(string)$existing->$column;
       if($old!==$row[$column]) throw new \RuntimeException('Mã '.$row['transaction_id'].' đã tồn tại với dữ liệu khác.');
      }
      if(number_format((float)$existing->amount,2,'.','')!==number_format((float)$row['amount'],2,'.','')) throw new \RuntimeException('Mã '.$row['transaction_id'].' trùng nhưng khác số tiền.');
      continue;
     }
     BankStatementTransaction::create($row+['imported_by'=>$request->user()->id]);$count++;
    }
   });
  }catch(\Throwable $e){return back()->with('error','Nhập sao kê thất bại: '.$e->getMessage());}
  return back()->with('success',"Đã nhập $count giao dịch mới. Dữ liệu sao kê cần được admin xác thực với ngân hàng trước khi duyệt.");
 }
}
