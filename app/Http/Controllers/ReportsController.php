<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request;
use DB,Validator,Session,Mail,Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Firm,App\FirmAddress,App\PotentialCaseInvoice;
use App\FirmEventReminder,App\FirmSolReminder,App\FlatFeeEntry;
use App\TaskTimeEntry,App\CaseMaster,App\TaskActivity,App\CaseTaskLinkedStaff;
use App\ExpenseEntry,App\RequestedFund,App\InvoiceAdjustment;
use App\Invoices,App\CaseClientSelection,App\UsersAdditionalInfo,App\CasePracticeArea,App\InvoicePayment;
use App\TimeEntryForInvoice,App\ExpenseForInvoice,App\SharedInvoice,App\InvoicePaymentPlan,App\InvoiceInstallment;
use App\InvoiceHistory,App\LeadAdditionalInfo,App\CaseStaff,App\InvoiceBatch,App\DepositIntoTrust,App\AllHistory,App\AccountActivity,App\DepositIntoCreditHistory,App\FlatFeeEntryForInvoice,App\TrustHistory;
use App\CaseStage,App\TempUserSelection,App\ClientNotes;
use App\InvoiceApplyTrustCreditFund;
use App\InvoiceCustomizationSetting;
use App\InvoiceOnlinePayment;
use App\InvoiceSetting;
use App\InvoiceTempInfo;
use App\Jobs\InvoiceReminderEmailJob;
use App\Jobs\OnlinePaymentEmailJob;
use App\RequestedFundOnlinePayment;
use App\Traits\CreditAccountTrait;
use App\Traits\InvoiceTrait;
use App\Traits\TrustAccountActivityTrait;
use App\Traits\TrustAccountTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use mikehaertl\wkhtmlto\Pdf;
// use PDF;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
class ReportsController extends BaseController
{
    
    public function __construct()
    {

    }
    
    public function accountsReceivableView(Request $request){
        
        $client_id = $request->client_id ?? '';
        $case_id = $request->case_id ?? '';
        $staff_id = $request->staff_id ?? '';
        $grp_by = $request->grp_by ?? '';
        $export_csv = $request->export_csv ?? '';
        $export_pdf = $request->export_pdf ?? '';
        $clientArray = [];
        $export_csv_path = "";
            
        if(isset($request->submit) && $request->submit != '' ){
            $authUser = auth()->user();
            
            $Invoices = Invoices::leftJoin("users","invoices.user_id","=","users.id")
            ->leftJoin("case_master","invoices.case_id","=","case_master.id")            
            ->leftJoin("case_practice_area","case_practice_area.id","=","case_master.practice_area")
            ->select('invoices.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as contact_name'),"users.user_level","users.id as uid","case_master.case_title as ctitle","case_master.case_unique_number","case_master.id as ccid","case_practice_area.title as practice_area_title")
            ->where("invoices.firm_id", $authUser->firm_name);
            $Invoices = $Invoices->whereNotIn("invoices.status", ['Paid','Forwarded']);
            if($client_id != ''){
                $Invoices = $Invoices->where("invoices.user_id",$client_id);
            }
            if($case_id != ''){
                $Invoices = $Invoices->where("invoices.case_id",$case_id);
            }

            if($staff_id != '' && $staff_id != 'all'){
                $Invoices = $Invoices->leftJoin("case_staff","case_staff.case_id","=","case_master.id");
                $Invoices = $Invoices->whereNull("case_staff.deleted_at");
                $Invoices = $Invoices->where("case_staff.lead_attorney",$staff_id);
            }
            $Invoices = $Invoices->where("invoices.due_amount" ,'>', 0);
            $Invoices = $Invoices->whereNotNull("invoices.case_id");
            $Invoices = $Invoices->orderBy('invoices.id', 'desc');
            $Invoices = $Invoices->get();   
            
            if($export_csv == 1 || $export_pdf == 1){
                $fileDestination = 'export/'.date('Y-m-d').'/'.Auth::User()->firm_name;
                $folderPath = public_path($fileDestination);

                File::deleteDirectory($folderPath);
                if(!is_dir($folderPath)) {
                    File::makeDirectory($folderPath, $mode = 0777, true, true);
                }    
                
                if(!File::isDirectory($folderPath)){
                    File::makeDirectory($folderPath, 0777, true, true);    
                }
                $casesCsvData[]="Invoice number|Client|Case|Invoice total|Amount paid|Amount receivable|Due date|Status|Days aging";
                
            }

            foreach($Invoices as $k=>$v){
                if($grp_by == 'client'){
                    $clientArray[$v->contact_name][] = $v;       
                }
                if($grp_by == 'case'){
                    $clientArray[$v->ctitle][] = $v;       
                }
                if($grp_by == 'practive_area'){
                    $clientArray[$v->practice_area_title][] = $v;       
                }
                if($export_csv == 1){
                    $dueDate = convertUTCToUserDate($v->due_date, $authUser->user_timezone ?? 'UTC')->format('Y-m-d');
                    $daysAging = ($v->due_date != Null) ? daysReturns($dueDate, 'onlyDays') : 0;
                    $casesCsvData[]=$v->invoice_id."|".$v->contact_name."|".$v->ctitle."|".$v->total_amount_new."|".$v->paid_amount_new."|".$v->due_amount_new."|".(($v->due_date!=NULL)? $v->due_date_new : '--')."|".$v->status."|".$daysAging;
                }
            }

            if($export_csv == 1){
                $file_path =  $folderPath.'/accounts_receivable_report.csv';  
                $file = fopen($file_path,"w+");
                foreach ($casesCsvData as $exp_data){
                fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
                }   
                fclose($file); 
                $export_csv_path = asset($fileDestination.'/accounts_receivable_report.csv');
            }

            if($export_pdf == 1){
                $file_path =  $folderPath.'/accounts_receivable_report.pdf';  
                $PDFData=view('reports.accounts_receivable.pdfview',  compact('Invoices','clientArray','request','client_id','case_id','staff_id','grp_by','export_csv_path'));
                $pdf = new Pdf;
                if($_SERVER['SERVER_NAME']=='localhost'){
                    $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
                }
                $pdf->addPage($PDFData);
                $pdf->setOptions(['javascript-delay' => 5000]);
                $pdf->saveAs($file_path);
                $export_csv_path = asset($fileDestination.'/accounts_receivable_report.pdf');
            }
        }else{
            $Invoices = [];
        }
        
        return view('reports.accounts_receivable.index', compact('Invoices','clientArray','request','client_id','case_id','staff_id','grp_by','export_csv_path'));
    }
    
    public function caseRevenueReportsView(Request $request){
        
        $from = $request->from ?? date('m/01/Y');
        $to = $request->to ?? date('m/d/Y');
        $case_status = $request->case_status ?? '';
        $lead_id = $request->lead_id ?? '';
        $staff_id = $request->staff_id ?? '';
        $practice_area = $request->practice_area ?? '';
        $office = $request->office ?? '';
        $billing_type = $request->billing_type ?? '';
        $lead_id = $request->lead_id ?? '';
        $export_csv = $request->export_csv ?? '';
        $show_case_with_daterange = $request->show_case_with_daterange ?? '';
        $export_pdf = $request->export_pdf ?? '';
        $export_csv_path = "";
        $clientArray = [];
        
        $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($from))))), auth()->user()->user_timezone ?? 'UTC')));
        $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim(($to)))))), auth()->user()->user_timezone ?? 'UTC')));
        
        $cases = CaseMaster::leftJoin('invoices','invoices.case_id','=','case_master.id')
        ->select('case_master.id', 'case_master.case_unique_number', 'case_master.case_title');
        if(auth()->user()->hasPermissionTo('access_all_cases')) { // Show cases as per user permission
            $cases = $cases->where('case_master.firm_id', auth()->user()->firm_name);
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',auth()->user()->id)->get()->pluck('case_id');
            $cases = $cases->whereIn("case_master.id",$childUSersCase);
        }

        if($case_status == 'close'){
            $cases = $cases->where("case_close_date","!=", NULL);
        }
        if($case_status == 'open'){
            $cases = $cases->where("case_close_date", NULL);
        }

        if($lead_id != 'all' || $staff_id != 'all'){
            $cases = $cases->leftJoin("case_staff","case_staff.case_id","=","case_master.id");
            $cases = $cases->whereNull("case_staff.deleted_at");
        }
        if($lead_id != '' && $lead_id != 'all'){
            $cases = $cases->where("case_staff.lead_attorney",$lead_id);
        }
        if($staff_id != '' && $staff_id != 'all'){
            $cases = $cases->where("case_staff.originating_attorney",$staff_id);
        }
        
        if($practice_area != '' && $practice_area != 'all'){
            $cases = $cases->where("case_master.practice_area",$practice_area);
        }

        if($office != '' && $office != 'all'){
            $cases = $cases->where("case_master.case_office",$office);
        }

        if($billing_type != '' && $billing_type != 'all'){
            $cases = $cases->where("case_master.billing_method",$billing_type);
        }

        $cases = $cases->where("case_master.is_entry_done","1");
        // $cases = $cases->whereIn("case_master.id",["175"]); 
        $cases = $cases->groupBy("case_master.id"); 
        if($export_csv == 1 || $export_pdf == 1){
            $cases = $cases->paginate(999999999)->appends(request()->except('page'));
        }else{
            $cases = $cases->paginate(200)->appends(request()->except('page'));
        }
        $invPaidRecors = [];
        if($export_csv == 1 || $export_pdf == 1){
            $fileDestination = 'export/'.date('Y-m-d').'/'.Auth::User()->firm_name;
            $folderPath = public_path($fileDestination);

            File::deleteDirectory($folderPath);
            if(!is_dir($folderPath)) {
                File::makeDirectory($folderPath, $mode = 0777, true, true);
            }    
            
            if(!File::isDirectory($folderPath)){
                File::makeDirectory($folderPath, 0777, true, true);    
            }
            $casesCsvData[]="Court Case Name|Billed Flat Fees|Billed Hours|Billed Time Entries|Billed Expenses|Billed Balances Forwarded|Billed Interest|Billed Tax|Billed Additions|Billed Discounts|Billed Write-offs|Non-billable Hours|Non-billable Amounts|Total Billed|Collected Flat Fees|Collected Time Entries|Collected Expenses|Collected Balances Forwarded|Collected Interest|Collected Tax|Collected Additions|Collected Discounts|Collected Write-offs|Total Collected";
            
        
            $totalCaseFlatfees = $totalCaseDuration = $totalCaseTimeEntry = $totalCaseExpenseEntry = 0;
            $totalCaseBalanceForwarded = $totalCaseInterestAdjustment = $totalCaseTaxAdjustment = $totalCaseAdditionsAdjustment = $totalCaseDiscountsAdjustment = 0;
            $totalCaseNonBillableDuration = $totalCaseNonBillableEntry =  $totalCaseBilled = 0;
            $totalPaidFlatfee = $totalPaidTimeEntry = $totalPaidExpenses = $totalPaidBalanceForward = $totalPaidInterest = $totalPaidTax = $totalPaidAdditions = $totalPaidDiscounts = 0;
            $totalcaseInvoicePaidAmount = 0;
            
        }
        $totalInvoiceEntry = 0;
        foreach($cases as $key => $case) {
            // \Log::info("Case id > ". $case->id . " > Case title > ". $case->case_title);
            // get Flat fee Entries list
            $FlatFeeEntry=FlatFeeEntry::leftJoin("users","flat_fee_entry.user_id","=","users.id")
            ->leftJoin("case_master","case_master.id","=","flat_fee_entry.case_id")
            ->leftJoin("invoices","invoices.id","=","flat_fee_entry.invoice_link")
            ->select('flat_fee_entry.*')->where('flat_fee_entry.status','paid')
            ->where("case_master.id",$case->id)->whereNull("invoices.deleted_at");
            $FlatFeeEntry = $FlatFeeEntry->whereBetween("flat_fee_entry.entry_date",[$startDt,$endDt]);
            $FlatFeeEntry = $FlatFeeEntry->get();
            
            $caseFlatfees = $caseNonBillableEntry = 0;
            foreach($FlatFeeEntry as $k => $v){
                if($v->time_entry_billable == "yes"){
                    $caseFlatfees += str_replace(",","",$v->cost);
                    if($v->case_id == $case->id){
                        $invPaidRecors[$case->id][$v->invoice_link]['flatFee'][] = str_replace(",","",$v->cost);
                        $totalInvoiceEntry += 1;
                        $invPaidRecors[$case->id][$v->invoice_link]['totalInvoiceEntry'] = $totalInvoiceEntry;

                    }
                }else{
                    $caseNonBillableEntry += str_replace(",","",$v->cost);
                }
            }
            $cases[$key]['caseFlatfees'] = number_format($caseFlatfees,2);
            // get Flat fee Entries list
            
            // get Time Entries list
            $TimeEntry=TaskTimeEntry::leftJoin("users","task_time_entry.user_id","=","users.id")
            ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
            ->leftJoin("case_master","case_master.id","=","task_time_entry.case_id")
            ->leftJoin("invoices","invoices.id","=","task_time_entry.invoice_link")
            ->select('task_time_entry.*')
            ->where("case_master.id",$case->id)->where("task_time_entry.status",'paid')->whereNull("invoices.deleted_at");
            $TimeEntry = $TimeEntry->whereBetween("task_time_entry.entry_date",[$startDt,$endDt]);
            $TimeEntry = $TimeEntry->get();
            
            $caseDuration = $caseTimeEntry = $caseNonBillableDuration = 0;
            foreach($TimeEntry as $k => $v){
                if($v->rate_type=="flat"){
                    if($v->time_entry_billable == "yes"){
                        $caseTimeEntry += str_replace(",","",$v->entry_rate);
                        $caseDuration += $v->duration;
                        if($v->case_id == $case->id){
                            $invPaidRecors[$case->id][$v->invoice_link]['timeEntry'][] = str_replace(",","",$v->entry_rate);
                            $totalInvoiceEntry += 1;
                            $invPaidRecors[$case->id][$v->invoice_link]['totalInvoiceEntry'] = $totalInvoiceEntry;
                        }
                    }else{
                        $caseNonBillableDuration += $v->duration;
                        $caseNonBillableEntry += str_replace(",","",$v->entry_rate);
                    }
                }else{
                    if($v->time_entry_billable =="yes"){
                        $caseDuration += $v->duration;
                        $caseTimeEntry += str_replace(",","",$v->duration) * str_replace(",","",$v->entry_rate);
                        if($v->case_id == $case->id){
                            $invPaidRecors[$case->id][$v->invoice_link]['timeEntry'][] = str_replace(",","",$v->duration) * str_replace(",","",$v->entry_rate);
                            $totalInvoiceEntry += 1;
                            $invPaidRecors[$case->id][$v->invoice_link]['totalInvoiceEntry'] = $totalInvoiceEntry;
                        }
                    }else{
                        $caseNonBillableDuration += $v->duration;
                        $caseNonBillableEntry += str_replace(",","",$v->duration) * str_replace(",","",$v->entry_rate);
                    }
                }                
            }
            $cases[$key]['caseDuration'] = $caseDuration;
            $cases[$key]['caseNonBillableDuration'] = $caseNonBillableDuration;
            $cases[$key]['caseTimeEntry'] = number_format($caseTimeEntry,2); 
            
            // get Time Entries list

            // get ExpenseEntry list
            $ExpenseEntry = ExpenseEntry::leftJoin("users","expense_entry.user_id","=","users.id")
            ->leftJoin("task_activity","task_activity.id","=","expense_entry.activity_id")
            ->leftJoin("case_master","case_master.id","=","expense_entry.case_id")
            ->leftJoin("invoices","invoices.id","=","expense_entry.invoice_link")
            ->select('expense_entry.*')
            ->where("case_master.id",$case->id)->where("expense_entry.status",'paid')
            ->whereNull("invoices.deleted_at");
            $ExpenseEntry = $ExpenseEntry->whereBetween("expense_entry.entry_date",[$startDt,$endDt]);
            $ExpenseEntry = $ExpenseEntry->get();
            $caseExpenseEntry = 0;
            foreach($ExpenseEntry as $k => $v){
                if($v->time_entry_billable == "yes"){
                    $caseExpenseEntry += str_replace(",","",$v->duration) * str_replace(",","",$v->cost);
                    if($v->case_id == $case->id){
                        $invPaidRecors[$case->id][$v->invoice_link]['expenseEntry'][] = str_replace(",","",$v->duration) * str_replace(",","",$v->cost);
                        $totalInvoiceEntry += 1;
                        $invPaidRecors[$case->id][$v->invoice_link]['totalInvoiceEntry'] = $totalInvoiceEntry;
                    }
                }else{
                    $caseNonBillableEntry += str_replace(",","",$v->duration) * str_replace(",","",$v->cost);
                }
            }
            $cases[$key]['caseExpenseEntry'] = number_format($caseExpenseEntry,2); 
            $cases[$key]['caseNonBillableEntry'] = number_format($caseNonBillableEntry,2); 
            // get ExpenseEntry list

            // get balance forwarded list

            $Invoices=Invoices::select('invoices.*')
            ->where("invoices.case_id",$case->id)->whereNull("invoices.deleted_at");
            // $Invoices = $Invoices->whereBetween("invoices.invoice_date",[$startDt,$endDt]);
            $Invoices = $Invoices->orderBy("invoices.id");            
            $Invoices = $Invoices->with('forwardedInvoices')->get();
            $caseBalanceForwarded = $caseInvoicePaidAmount =  0;
            foreach($Invoices as $k => $v){
                if($v->status == 'Forwarded'){
                    $caseBalanceForwarded += str_replace(",","",$v->due_amount);
                    if($v->case_id == $case->id){
                        $invPaidRecors[$case->id][$v->id]['forwardedEntry'][] = str_replace(",","",$v->due_amount);
                        $totalInvoiceEntry += 1;
                        $invPaidRecors[$case->id][$v->id]['totalInvoiceEntry'] = $totalInvoiceEntry;
                    }
                }
                // $selectedFwdInv = [];
                // if(isset($v->forwardedInvoices) && count($v->forwardedInvoices)) {
                //     $selectedFwdInv = $v->forwardedInvoices->pluck("id")->toArray();
                // }
                // if(count($selectedFwdInv) > 0) {
                //     \Log::info("selectedFwdInv");
                //     foreach($selectedFwdInv as $i => $j ){    
                //         if(isset($invPaidRecors[$case->id][$j]['paidAmount'])){             
                //             \Log::info(" selectedFwdInv > amount >". array_sum($invPaidRecors[$case->id][$j]['paidAmount']));
                //             // $invPaidRecors[$case->id][$v->id]['forwardedEntry'][] = str_replace(",","",array_sum($invPaidRecors[$case->id][$j]['paidAmount']));
                //         }
                //     }
                // }
                $caseInvoicePaidAmount += str_replace(",","",$v->paid_amount);
                if(str_replace(",","",$v->paid_amount) > 0){
                    if($v->case_id == $case->id){
                        $invoicePayData = InvoiceHistory::where('invoice_id', $v->id)->whereDate("created_at",'>=',$startDt)->whereDate("created_at",'<=',$endDt)->whereIn('status',['1','2','3','4','6'])->whereIn('acrtivity_title',['Payment Received','Payment Refund'])->select('amount','status')->get();
                        $invoicePayment = 0;
                        foreach($invoicePayData as $payment){
                            if ($payment->amount && $payment->amount > 0) {
                                if ($payment->status != 4) {
                                    $invoicePayment += $payment->amount;
                                } else {
                                    $invoicePayment -= $payment->amount;
                                }
                            }
                        }
                        $invPaidRecors[$case->id][$v->id]['forwardedEntry'][] = $caseBalanceForwarded;
                        $invPaidRecors[$case->id][$v->id]['paidAmount'][] = str_replace(",","",$invoicePayment);
                        $invPaidRecors[$case->id][$v->id]['totalAmount'][] = str_replace(",","",$v->total_amount);
                    }
                }
            }           
            $cases[$key]['caseInvoicePaidAmount'] = number_format($caseInvoicePaidAmount,2);
            $cases[$key]['caseBalanceForwarded'] = number_format($caseBalanceForwarded,2);
            // get balance forwarded list

            // get adjustment amount list
            
            $InvoiceAdjustment=InvoiceAdjustment::leftJoin("invoices","invoices.id","=","invoice_adjustment.invoice_id")
            ->select('invoice_adjustment.*')
            ->where("invoice_adjustment.case_id",$case->id)->whereNull("invoices.deleted_at")
            ->whereNotNull("invoice_adjustment.invoice_id");
            // $InvoiceAdjustment = $InvoiceAdjustment->whereBetween("invoice_adjustment.created_at",[$startDt,$endDt]);
            $InvoiceAdjustment = $InvoiceAdjustment->whereDate("invoice_adjustment.created_at", '>=', $startDt)->whereDate("invoice_adjustment.created_at", '<=', $endDt);
            $InvoiceAdjustment = $InvoiceAdjustment->get();
            $caseInterestAdjustment = $caseTaxAdjustment = $caseAdditionsAdjustment = $caseDiscountsAdjustment = 0;
            foreach($InvoiceAdjustment as $k => $v){
                switch ($v->item) {
                    case 'discount':
                        if($v->ad_type=="amount"){
                            $invoiceAmount = str_replace(",","",$v->amount);
                        }else{
                            $invoiceAmount = (str_replace(",","",$v->basis) * $v->percentages ) / 100; 
                        }
                        if($v->invoice_id != null){
                            if($v->case_id == $case->id){
                                $invPaidRecors[$case->id][$v->invoice_id]['discountAmount'][] = $invoiceAmount;
                                $totalInvoiceEntry += 1;
                                $invPaidRecors[$case->id][$v->invoice_id]['totalInvoiceEntry'] = $totalInvoiceEntry;
                            }
                        }
                        $caseDiscountsAdjustment += str_replace(",","",$invoiceAmount);
                        break;
                    case 'intrest':
                        if($v->ad_type=="amount"){
                            $invoiceAmount = str_replace(",","",$v->amount);
                        }else{
                            $invoiceAmount = (str_replace(",","",$v->basis) * $v->percentages ) / 100; 
                        }
                        $caseInterestAdjustment += str_replace(",","",$invoiceAmount);
                        if($v->invoice_id != null){
                            if($v->case_id == $case->id){
                                $invPaidRecors[$case->id][$v->invoice_id]['interestAmount'][] = $invoiceAmount;
                                $totalInvoiceEntry += 1;
                                $invPaidRecors[$case->id][$v->invoice_id]['totalInvoiceEntry'] = $totalInvoiceEntry;
                            }
                        }
                        break;
                    case 'tax':
                        if($v->ad_type=="amount"){
                            $invoiceAmount = str_replace(",","",$v->amount);
                        }else{
                            $invoiceAmount = (str_replace(",","",$v->basis) * $v->percentages ) / 100; 
                        }
                        $caseTaxAdjustment += str_replace(",","",$invoiceAmount);
                        if($v->invoice_id != null){
                            if($v->case_id == $case->id){
                                $invPaidRecors[$case->id][$v->invoice_id]['taxAmount'][] = $invoiceAmount;
                                $totalInvoiceEntry += 1;
                                $invPaidRecors[$case->id][$v->invoice_id]['totalInvoiceEntry'] = $totalInvoiceEntry;
                            }
                        }
                        break;
                    case 'addition':
                        if($v->ad_type=="amount"){
                            $invoiceAmount = str_replace(",","",$v->amount);
                        }else{
                            $invoiceAmount = (str_replace(",","",$v->basis) * $v->percentages ) / 100; 
                        }
                        $caseAdditionsAdjustment += str_replace(",","",$invoiceAmount);
                        if($v->invoice_id != null){
                            if($v->case_id == $case->id){
                                $invPaidRecors[$case->id][$v->invoice_id]['additionAmount'][] = $invoiceAmount;
                                $totalInvoiceEntry += 1;
                                $invPaidRecors[$case->id][$v->invoice_id]['totalInvoiceEntry'] = $totalInvoiceEntry;
                            }
                        }
                        break;
                    default:
                        break;
                }                
            }
            
            $cases[$key]['caseInterestAdjustment'] = number_format($caseInterestAdjustment,2);
            $cases[$key]['caseTaxAdjustment'] = number_format($caseTaxAdjustment,2);
            $cases[$key]['caseAdditionsAdjustment'] = number_format($caseAdditionsAdjustment,2);
            $cases[$key]['caseDiscountsAdjustment'] = number_format($caseDiscountsAdjustment,2);
            
            // dd($invPaidRecors);
            // get adjustment amount list
            // $cases[$key]['invPaidRecors'] = $invPaidRecors;
            $payFlatfee = $payTimeEntry = $payExpenses = $payBalanceForward = $payInterest = $payTax = $payAdditions = $payDiscounts = 0;
            foreach($invPaidRecors as $kk => $inv){
                // echo "<pre>";
                if($kk > 0 && $kk == $case->id){
                    foreach($inv as $jj => $val){
                        if(isset($val['paidAmount']) && $val['paidAmount']> 0){
                            $totalPaidInvoice = array_sum(str_replace(",","",$val['paidAmount']));
                            $totalInvoiceCount = $val['totalInvoiceEntry'] ?? 0;
                            // \Log::info("case_id > ".$case->id." > totalPaidInvoice > ".$totalPaidInvoice." > totalAmount > ".$val['totalAmount'][0]);
                            if($totalPaidInvoice > 0){
                                $totalDeductPercentage = (($totalPaidInvoice / (($val['totalAmount'][0] > 0) ? $val['totalAmount'][0] : $totalPaidInvoice)) * 100);
                            }else{
                                $totalDeductPercentage = 100;
                            }
                            // echo  "totalPaidInvoice > ".$totalPaidInvoice.' > <br>';
                            if(isset($val['flatFee'])){                            
                                if($totalPaidInvoice > 0){
                                    // if($totalPaidInvoice >= array_sum($val['flatFee'])){
                                    //     $payFlatfee += str_replace(",","",((array_sum($val['flatFee']) / $totalInvoiceCount)));
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payFlatfee;
                                    // }else{
                                    //     $payFlatfee += $totalPaidInvoice;
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payFlatfee;
                                    // }
                                    $payFlatfee += str_replace(",","",((array_sum($val['flatFee']) * $totalDeductPercentage) / 100));
                                    // $totalPaidInvoice = $totalPaidInvoice - $payFlatfee;
                                }
                            }
                            if(isset($val['timeEntry'])){
                                // echo  array_sum($val['timeEntry']).' > <br>';
                                if($totalPaidInvoice > 0){
                                    // if($totalPaidInvoice >= array_sum($val['timeEntry'])){
                                    //     $payTimeEntry += str_replace(",","",((array_sum($val['timeEntry']) / $totalInvoiceCount)));
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payTimeEntry;
                                    // }else{
                                    //     $payTimeEntry += $totalPaidInvoice;
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payTimeEntry;
                                    // }
                                    $payTimeEntry += str_replace(",","",((array_sum($val['timeEntry'])  * $totalDeductPercentage) / 100));
                                    // $totalPaidInvoice = $totalPaidInvoice - $payTimeEntry;
                                }
                                // print_r($payTimeEntry);
                            }
                            if(isset($val['expenseEntry'])){
                                if($totalPaidInvoice > 0){
                                    // if($totalPaidInvoice > array_sum($val['expenseEntry'])){
                                    //     $payExpenses += str_replace(",","",array_sum($val['expenseEntry']));
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payExpenses;
                                    // }else{
                                    //     $payExpenses += $totalPaidInvoice;
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payExpenses;
                                    // }

                                    $payExpenses += str_replace(",","",((array_sum($val['expenseEntry'])  * $totalDeductPercentage) / 100));
                                    // $totalPaidInvoice = $totalPaidInvoice - $payExpenses;
                                }
                            }
                            if(isset($val['interestAmount'])){
                                if($totalPaidInvoice > 0){
                                    // if($totalPaidInvoice > array_sum($val['interestAmount'])){
                                    //     $payInterest += str_replace(",","",array_sum($val['interestAmount']));
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payInterest;
                                    // }else{
                                    //     $payInterest += $totalPaidInvoice;
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payInterest;
                                    // }
                                    $payInterest += str_replace(",","",((array_sum($val['interestAmount'])  * $totalDeductPercentage) / 100));
                                    // $totalPaidInvoice = $totalPaidInvoice - $payInterest;
                                }
                            }
                            if(isset($val['taxAmount'])){
                                if($totalPaidInvoice > 0){
                                    // if($totalPaidInvoice > array_sum($val['taxAmount'])){
                                    //     $payTax += str_replace(",","",array_sum($val['taxAmount']));
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payTax;
                                    // }else{
                                    //     $payTax += $totalPaidInvoice;
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payTax;
                                    // }
                                    $payTax += str_replace(",","",((array_sum($val['taxAmount'])  * $totalDeductPercentage) / 100));
                                    // $totalPaidInvoice = $totalPaidInvoice - $payTax;
                                    
                                }
                            }
                            if(isset($val['additionAmount'])){
                                if($totalPaidInvoice > 0){
                                    // if($totalPaidInvoice > array_sum($val['additionAmount'])){
                                    //     $payAdditions += str_replace(",","",array_sum($val['additionAmount']));
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payAdditions;
                                    // }else{
                                    //     $payAdditions += $totalPaidInvoice;
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payAdditions;
                                    // }
                                    $payAdditions += str_replace(",","",((array_sum($val['additionAmount'])  * $totalDeductPercentage) / 100));
                                    // $totalPaidInvoice = $totalPaidInvoice - $payAdditions;
                                }
                            }
                            if(isset($val['discountAmount'])){
                                if($totalPaidInvoice > 0){
                                    // if($totalPaidInvoice > array_sum($val['discountAmount'])){
                                    //     $payDiscounts += str_replace(",","",array_sum($val['discountAmount']));
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payDiscounts;
                                    // }else{
                                    //     $payDiscounts += $totalPaidInvoice;
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payDiscounts;
                                    // }
                                    $payDiscounts += str_replace(",","",((array_sum($val['discountAmount'])  * $totalDeductPercentage) / 100));
                                    // $totalPaidInvoice = $totalPaidInvoice - $payDiscounts;
                                }
                            }                            
                            if(isset($val['forwardedEntry'])){
                                if($totalPaidInvoice > 0){
                                    // if($totalPaidInvoice > array_sum($val['forwardedEntry'])){
                                    //     $payBalanceForward += str_replace(",","",array_sum($val['forwardedEntry']));
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payBalanceForward;
                                    // }else{
                                    //     $payBalanceForward += $totalPaidInvoice;
                                    //     $totalPaidInvoice = $totalPaidInvoice - $payBalanceForward;
                                    // }
                                    $payBalanceForward += str_replace(",","",((array_sum($val['forwardedEntry'])  * $totalDeductPercentage) / 100));
                                    // $totalPaidInvoice = $totalPaidInvoice - $payBalanceForward;
                                }
                            }
                        }
                    }
                }
            }
            $cases[$key]['paidFlatfee'] = $payFlatfee;
            $cases[$key]['paidTimeEntry'] = $payTimeEntry;
            $cases[$key]['paidExpenses'] = $payExpenses;
            $cases[$key]['paidBalanceForward'] = $payBalanceForward;            
            $cases[$key]['paidDiscounts'] = $payDiscounts;
            $cases[$key]['paidInterest'] = $payInterest;
            $cases[$key]['paidTax'] = $payTax;
            $cases[$key]['paidAdditions'] = $payAdditions;

            // dd($invPaidRecors);
            if($export_csv == 1){
                $totalCaseFlatfees += str_replace(",","",$case->caseFlatfees); 
                $totalCaseDuration += str_replace(",","",$case->caseDuration);
                $totalCaseTimeEntry += str_replace(",","",$case->caseTimeEntry);
                $totalCaseExpenseEntry += str_replace(",","",$case->caseExpenseEntry);
                
                $totalCaseInterestAdjustment += str_replace(",","",$case->caseInterestAdjustment);
                $totalCaseTaxAdjustment += str_replace(",","",$case->caseTaxAdjustment);
                $totalCaseAdditionsAdjustment += str_replace(",","",$case->caseAdditionsAdjustment);
                $totalCaseDiscountsAdjustment += str_replace(",","",$case->caseDiscountsAdjustment);
                $totalCaseNonBillableDuration += str_replace(",","",$case->caseNonBillableDuration);
                $totalCaseNonBillableEntry += str_replace(",","",$case->caseNonBillableEntry);
               
                $totalBilled  = str_replace(",","",$case->caseFlatfees) + str_replace(",","",$case->caseTimeEntry) + str_replace(",","",$case->caseExpenseEntry) + str_replace(",","",$case->caseInterestAdjustment) + str_replace(",","",$case->caseTaxAdjustment) + str_replace(",","",$case->caseAdditionsAdjustment)  + str_replace(",","",$case->caseNonBillableEntry) -  str_replace(",","",$case->caseDiscountsAdjustment) - str_replace(",","",$case->caseNonBillableEntry);
                $totalCaseBilled += $totalBilled;

                // $totalPaidInvoice = str_replace(",","",$case->caseInvoicePaidAmount);
                // $totalcaseInvoicePaidAmount +=$totalPaidInvoice;
                // collected amount
                $totalPaidFlatfee += str_replace(",","",$case->paidFlatfee);
                $totalPaidTimeEntry += str_replace(",","",$case->paidTimeEntry);
                $totalPaidExpenses += str_replace(",","",$case->paidExpenses);
                $totalPaidInterest += str_replace(",","",$case->paidInterest);
                $totalPaidTax += str_replace(",","",$case->paidTax);
                $totalPaidAdditions += str_replace(",","",$case->paidAdditions);
                $totalPaidDiscounts += str_replace(",","",$case->paidDiscounts);

                $totalCollected = str_replace(",","",$case->paidFlatfee)
                + str_replace(",","",$case->paidTimeEntry)
                + str_replace(",","",$case->paidExpenses)
                + str_replace(",","",$case->paidInterest)
                + str_replace(",","",$case->paidTax)
                + str_replace(",","",$case->paidAdditions)
                - str_replace(",","",$case->paidDiscounts);
                $totalcaseInvoicePaidAmount = $totalPaidFlatfee + $totalPaidTimeEntry + $totalPaidExpenses + $totalPaidBalanceForward + $totalPaidInterest + $totalPaidTax + $totalPaidAdditions - $totalPaidDiscounts;

                if($totalBilled > 0 && $show_case_with_daterange == 'on'){  
                    $casesCsvData[]= $case->case_title."|$".number_format($caseFlatfees,2)."|".$caseDuration."|$".number_format($caseTimeEntry,2)."|$".number_format($caseExpenseEntry,2)."|$".number_format($caseBalanceForwarded,2)."|$".number_format($caseInterestAdjustment,2)."|$".number_format($caseTaxAdjustment,2)."|$".number_format($caseAdditionsAdjustment,2)."|$-".number_format($caseDiscountsAdjustment,2)."|$0.00|".$caseNonBillableDuration."|$".$case->caseNonBillableEntry."|$".number_format($totalBilled,2)."|$".number_format($case->paidFlatfee,2)."|$".number_format($case->paidTimeEntry,2)."|$".number_format($case->paidExpenses,2)."|$".number_format($case->paidBalanceForward,2)."|$".number_format($case->paidInterest,2)."|$".number_format($case->paidTax,2)."|$".number_format($case->paidAdditions,2)."|$-".number_format($case->paidDiscounts,2)."|$0.00|$".number_format(str_replace(",","",$totalCollected),2);
                }else{             
                    if($totalBilled >= 0){   
                        $casesCsvData[]= $case->case_title."|$".number_format($caseFlatfees,2)."|".$caseDuration."|$".number_format($caseTimeEntry,2)."|$".number_format($caseExpenseEntry,2)."|$".number_format($caseBalanceForwarded,2)."|$".number_format($caseInterestAdjustment,2)."|$".number_format($caseTaxAdjustment,2)."|$".number_format($caseAdditionsAdjustment,2)."|$-".number_format($caseDiscountsAdjustment,2)."|$0.00|".$caseNonBillableDuration."|$".$case->caseNonBillableEntry."|$".number_format($totalBilled,2)."|$".number_format($case->paidFlatfee,2)."|$".number_format($case->paidTimeEntry,2)."|$".number_format($case->paidExpenses,2)."|$".number_format($case->paidBalanceForward,2)."|$".number_format($case->paidInterest,2)."|$".number_format($case->paidTax,2)."|$".number_format($case->paidAdditions,2)."|$-".number_format($case->paidDiscounts,2)."|$0.00|$".number_format(str_replace(",","",$totalCollected),2);
                    }
                }
                
            }
        }
        foreach($cases as $k => $case) {
            $totalBilled  = str_replace(",","",$case->caseFlatfees) + str_replace(",","",$case->caseTimeEntry) + str_replace(",","",$case->caseExpenseEntry) + str_replace(",","",$case->caseInterestAdjustment) + str_replace(",","",$case->caseTaxAdjustment) + str_replace(",","",$case->caseAdditionsAdjustment)  + str_replace(",","",$case->caseNonBillableEntry) -  str_replace(",","",$case->caseDiscountsAdjustment) - str_replace(",","",$case->caseNonBillableEntry);
            if($totalBilled == 0){            
                if($show_case_with_daterange == 'on'){
                    unset($cases[$k]);
                }
            }
        }
        
        if($export_csv == 1){
            if(count($cases) > 0){
                $casesCsvData[]="Page Total (Sum of the ".count($cases)." rows displayed)|$".number_format($totalCaseFlatfees,2)."|".$totalCaseDuration."|$".number_format($totalCaseTimeEntry,2)."|$".number_format($totalCaseExpenseEntry,2)."|$".number_format($totalCaseBalanceForwarded,2)."|$".number_format($totalCaseInterestAdjustment,2)."|$".number_format($totalCaseTaxAdjustment,2)."|$".number_format($totalCaseAdditionsAdjustment,2)."|$-".number_format($totalCaseDiscountsAdjustment,2)."|$0.00|".number_format($totalCaseNonBillableDuration,2)."|$".number_format($totalCaseNonBillableEntry,2)."|$".number_format($totalCaseBilled,2)."|$".number_format($totalPaidFlatfee,2)."|$".number_format($totalPaidTimeEntry,2)."|$".number_format($totalPaidExpenses,2)."|$".number_format($totalPaidBalanceForward,2)."|$".number_format($totalPaidInterest,2)."|$".number_format($totalPaidTax,2)."|$".number_format($totalPaidAdditions,2)."|$-".number_format($totalPaidDiscounts,2)."|$0.00|$".number_format($totalcaseInvoicePaidAmount,2);
            }
        }
        if($export_csv == 1){
            $file_path =  $folderPath.'/'.str_replace("/","-",$from).'_to_'.str_replace("/","-",$to).'_case_revenue_reports.csv';  
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
            fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }   
            fclose($file); 
            $export_csv_path = asset($fileDestination.'/'.str_replace("/","-",$from).'_to_'.str_replace("/","-",$to).'_case_revenue_reports.csv');
        }
        if($export_pdf == 1){
            $file_path =  $folderPath.'/'.str_replace("/","-",$from).'_to_'.str_replace("/","-",$to).'_case_revenue_reports.pdf';  
            $PDFData=view('reports.case_revenue_reports.pdfview', compact("from", "to", "cases"));
            $pdf = new Pdf;
            if($_SERVER['SERVER_NAME']=='localhost'){
                $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
            }
            $pdf->addPage($PDFData);
            $pdf->setOptions(['javascript-delay' => 5000,'orientation' => 'landscape']);
            $pdf->saveAs($file_path);
            $export_csv_path = $file_path;
            $export_csv_path = asset($fileDestination.'/'.str_replace("/","-",$from).'_to_'.str_replace("/","-",$to).'_case_revenue_reports.pdf');
        }
        // dd($cases);      
        // return $cases;
        return view('reports.case_revenue_reports.index', compact("from", "to", "case_status","staff_id", "practice_area", "office", "billing_type", "lead_id", "export_csv_path", "cases", "show_case_with_daterange"));
    }
}
  