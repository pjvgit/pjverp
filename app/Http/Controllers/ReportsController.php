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
            $Invoices = $Invoices->orderBy('invoices.id', 'desc');
            $Invoices = $Invoices->get();   
            
            if($export_csv == 1){
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
                    $casesCsvData[]=$v->invoice_id."|".$v->contact_name."|".$v->ctitle."|".$v->total_amount_new."|".$v->paid_amount_new."|".$v->due_amount_new."|".(($v->due_date!=NULL)? $v->due_date : '--')."|".$v->status."|".$v->days_aging;
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
        
        $export_csv_path = "";
        $clientArray = [];
        
        $startDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim($from))))), auth()->user()->user_timezone ?? 'UTC')));
        $endDt =  date('Y-m-d',strtotime(convertDateToUTCzone(date("Y-m-d", strtotime(date('Y-m-d',strtotime(trim(($to)))))), auth()->user()->user_timezone ?? 'UTC')));
        
        $cases = CaseMaster::select('case_master.id', 'case_master.case_unique_number', 'case_master.case_title');
        if(auth()->user()->hasPermissionTo('access_all_cases')) { // Show cases as per user permission
            $cases = $cases->where('case_master.firm_id', auth()->user()->firm_name);
        }else{
            $childUSersCase = CaseStaff::select("case_id")->where('user_id',auth()->user()->id)->get()->pluck('case_id');
            $cases = $cases->whereIn("case_master.id",$childUSersCase);
        }
        
        if($show_case_with_daterange == 'on'){
            $cases = $cases->whereBetween("case_master.case_open_date",[$startDt,$endDt]);
        }
        
        if($case_status == 'close'){
            $cases = $cases->where("case_close_date","!=", NULL);
        }
        if($case_status == 'open'){
            $cases = $cases->where("case_close_date", NULL);
        }

        if($lead_id != '' && $lead_id != 'all'){
            $cases = $cases->leftJoin("case_staff","case_staff.case_id","=","case_master.id");
            $cases = $cases->whereNull("case_staff.deleted_at");
            $cases = $cases->where("case_staff.lead_attorney",$lead_id);
        }

        if($staff_id != '' && $staff_id != 'all'){
            $cases = $cases->leftJoin("case_staff","case_staff.case_id","=","case_master.id");
            $cases = $cases->whereNull("case_staff.deleted_at");
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
        // $cases = $cases->where("case_master.id","6"); 
        $cases = $cases->groupBy("case_master.id"); 
        $cases = $cases->get();


        if($export_csv == 1){
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
            
        }
        foreach($cases as $key => $case) {
            // get Flat fee Entries list
            $FlatFeeEntry=FlatFeeEntry::leftJoin("users","flat_fee_entry.user_id","=","users.id")
            ->leftJoin("case_master","case_master.id","=","flat_fee_entry.case_id")
            ->leftJoin("invoices","invoices.id","=","flat_fee_entry.invoice_link")
            ->select('flat_fee_entry.*')
            ->where("case_master.id",$case->id);
            if($show_case_with_daterange == 'on'){
                $FlatFeeEntry = $FlatFeeEntry->whereBetween("flat_fee_entry.entry_date",[$startDt,$endDt]);
            }            
            $FlatFeeEntry = $FlatFeeEntry->get();
            
            $caseFlatfees =  0;
            foreach($FlatFeeEntry as $k => $v){
                $caseFlatfees += str_replace(",","",$v->cost);
            }
            $cases[$key]['caseFlatfees'] = number_format($caseFlatfees,2);
            // get Flat fee Entries list
            
            // get Time Entries list
            $TimeEntry=TaskTimeEntry::leftJoin("users","task_time_entry.user_id","=","users.id")
            ->leftJoin("task_activity","task_activity.id","=","task_time_entry.activity_id")
            ->leftJoin("case_master","case_master.id","=","task_time_entry.case_id")
            ->leftJoin("invoices","invoices.id","=","task_time_entry.invoice_link")
            ->select('task_time_entry.*')
            ->where("case_master.id",$case->id);
            if($show_case_with_daterange == 'on'){
                $TimeEntry = $TimeEntry->whereBetween("task_time_entry.entry_date",[$startDt,$endDt]);
            }            
            $TimeEntry = $TimeEntry->get();
            
            $caseDuration = $caseTimeEntry = $caseNonBillableDuration =  $caseNonBillableEntry = 0;
            foreach($TimeEntry as $k => $v){
                if($v->rate_type=="flat"){
                    if($v->time_entry_billable == "yes"){
                        $caseTimeEntry += str_replace(",","",$v->entry_rate);
                        $caseDuration += $v->duration;
                    }else{
                        $caseNonBillableDuration += $v->duration;
                        $caseNonBillableEntry += str_replace(",","",$v->entry_rate);
                    }
                }else{
                    if($v->time_entry_billable =="yes"){
                        $caseTimeEntry += str_replace(",","",$v->duration) * str_replace(",","",$v->entry_rate);
                    }else{
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
            ->select('expense_entry.*')
            ->where("case_master.id",$case->id);
            if($show_case_with_daterange == 'on'){
                $ExpenseEntry = $ExpenseEntry->whereBetween("expense_entry.entry_date",[$startDt,$endDt]);
            }            
            $ExpenseEntry = $ExpenseEntry->get();
            
            $caseExpenseEntry = $caseNonBillableEntry = 0;
            foreach($ExpenseEntry as $k => $v){
                if($v->time_entry_billable == "yes"){
                    $caseExpenseEntry += str_replace(",","",$v->duration) * str_replace(",","",$v->cost);
                }else{
                    $caseNonBillableEntry += str_replace(",","",$v->duration) * str_replace(",","",$v->cost);
                }
            }
            $cases[$key]['caseExpenseEntry'] = number_format($caseExpenseEntry,2); 
            $cases[$key]['caseNonBillableEntry'] = number_format($caseNonBillableEntry,2); 
            // get ExpenseEntry list

            // get balance forwarded list

            $Invoices=Invoices::select('invoices.*')
            ->where("invoices.case_id",$case->id);
            if($show_case_with_daterange == 'on'){
                $Invoices = $Invoices->whereBetween("invoices.invoice_date",[$startDt,$endDt]);
            }            
            $Invoices = $Invoices->get();
            $caseBalanceForwarded = $caseInvoicePaidAmount =  0;
            foreach($Invoices as $k => $v){
                if($v->status == 'forwarded'){
                    $caseBalanceForwarded += str_replace(",","",$v->total_amount);
                }
                $caseInvoicePaidAmount += str_replace(",","",$v->paid_amount);
            }
            $cases[$key]['caseInvoicePaidAmount'] = number_format($caseInvoicePaidAmount,2);
            $cases[$key]['caseBalanceForwarded'] = number_format($caseBalanceForwarded,2);
            // get balance forwarded list

            // get adjustment amount list
            
            $InvoiceAdjustment=InvoiceAdjustment::select('invoice_adjustment.*')
            ->where("invoice_adjustment.case_id",$case->id);
            if($show_case_with_daterange == 'on'){
                $InvoiceAdjustment = $InvoiceAdjustment->whereBetween("invoice_adjustment.created_at",[$startDt,$endDt]);
            }            
            $InvoiceAdjustment = $InvoiceAdjustment->get();
            $caseInterestAdjustment = $caseTaxAdjustment = $caseAdditionsAdjustment = $caseDiscountsAdjustment = 0;
            foreach($InvoiceAdjustment as $k => $v){
                switch ($v->item) {
                    case 'discount':
                        if($v->ad_type=="amount"){
                            $invoiceAmount = $v->basis;
                        }else{
                            $invoiceAmount = ($v->basis * $v->percentages ) / 100; 
                        }
                        $caseDiscountsAdjustment += str_replace(",","",$invoiceAmount);
                        break;
                    case 'intrest':
                        if($v->ad_type=="amount"){
                            $invoiceAmount = $v->basis;
                        }else{
                            $invoiceAmount = ($v->basis * $v->percentages ) / 100; 
                        }
                        $caseInterestAdjustment += str_replace(",","",$invoiceAmount);
                        break;
                    case 'tax':
                        if($v->ad_type=="amount"){
                            $invoiceAmount = $v->basis;
                        }else{
                            $invoiceAmount = ($v->basis * $v->percentages ) / 100; 
                        }
                        $caseTaxAdjustment += str_replace(",","",$invoiceAmount);
                        break;
                    case 'addition':
                        if($v->ad_type=="amount"){
                            $invoiceAmount = $v->basis;
                        }else{
                            $invoiceAmount = ($v->basis * $v->percentages ) / 100; 
                        }
                        $caseAdditionsAdjustment += str_replace(",","",$invoiceAmount);
                        break;
                    default:
                        break;
                }
                
            }
            $cases[$key]['caseInterestAdjustment'] = number_format($caseInterestAdjustment,2);
            $cases[$key]['caseTaxAdjustment'] = number_format($caseTaxAdjustment,2);
            $cases[$key]['caseAdditionsAdjustment'] = number_format($caseAdditionsAdjustment,2);
            $cases[$key]['caseDiscountsAdjustment'] = number_format($caseDiscountsAdjustment,2);
            // get adjustment amount list

            if($export_csv == 1){
                $totalCaseFlatfees += str_replace(",","",$case->caseFlatfees); 
                $totalCaseDuration += str_replace(",","",$case->caseDuration);
                $totalCaseTimeEntry += str_replace(",","",$case->caseTimeEntry);
                $totalCaseExpenseEntry += str_replace(",","",$case->caseExpenseEntry);
                $totalCaseBalanceForwarded += str_replace(",","",$case->caseBalanceForwarded);
                $totalCaseInterestAdjustment += str_replace(",","",$case->caseInterestAdjustment);
                $totalCaseTaxAdjustment += str_replace(",","",$case->caseTaxAdjustment);
                $totalCaseAdditionsAdjustment += str_replace(",","",$case->caseAdditionsAdjustment);
                $totalCaseDiscountsAdjustment += str_replace(",","",$case->caseDiscountsAdjustment);
                $totalCaseNonBillableDuration += str_replace(",","",$case->caseNonBillableDuration);
                $totalCaseNonBillableEntry += str_replace(",","",$case->caseNonBillableEntry);
                $totalBilled  = str_replace(",","",$case->caseFlatfees) + str_replace(",","",$case->caseTimeEntry) + str_replace(",","",$case->caseExpenseEntry) + str_replace(",","",$case->caseBalanceForwarded) + str_replace(",","",$case->caseInterestAdjustment) + str_replace(",","",$case->caseTaxAdjustment) + str_replace(",","",$case->caseAdditionsAdjustment) + str_replace(",","",$case->caseDiscountsAdjustment) + str_replace(",","",$case->caseNonBillableEntry);
                $totalCaseBilled += str_replace(",","",$case->caseFlatfees) + str_replace(",","",$case->caseTimeEntry) + str_replace(",","",$case->caseExpenseEntry) + str_replace(",","",$case->caseBalanceForwarded) + str_replace(",","",$case->caseInterestAdjustment) + str_replace(",","",$case->caseTaxAdjustment) + str_replace(",","",$case->caseAdditionsAdjustment) + str_replace(",","",$case->caseDiscountsAdjustment) + str_replace(",","",$case->caseNonBillableEntry);

                $totalPaidInvoice = str_replace(",","",$case->caseInvoicePaidAmount);
                $paidFlatfee = $paidTimeEntry = $paidExpenses = $paidBalanceForward = $paidInterest = $paidTax = $paidAdditions = $paidDiscounts = 0;
                
                
                if(str_replace(",","",$case->caseFlatfees) > 0 && $totalPaidInvoice > 0){
                    if($totalPaidInvoice > str_replace(",","",$case->caseFlatfees)){
                        $paidFlatfee = str_replace(",","",$case->caseFlatfees);
                        $totalPaidInvoice -= $paidFlatfee;
                    }else{
                        $paidFlatfee = $totalPaidInvoice;
                        $totalPaidInvoice -= $paidFlatfee;
                    }
                    $totalPaidFlatfee += $paidFlatfee;
                }     
                if(str_replace(",","",$case->caseTimeEntry) > 0 && $totalPaidInvoice > 0){
                    if($totalPaidInvoice > str_replace(",","",$case->caseTimeEntry)){
                        $paidTimeEntry = str_replace(",","",$case->caseTimeEntry);
                        $totalPaidInvoice -= $paidTimeEntry;
                    }else{
                        $paidTimeEntry = $totalPaidInvoice;
                        $totalPaidInvoice -= $paidTimeEntry;
                    }
                    $totalPaidTimeEntry += $paidTimeEntry;
                }    
                if(str_replace(",","",$case->caseExpenseEntry) > 0  && $totalPaidInvoice > 0){
                    if($totalPaidInvoice > str_replace(",","",$case->caseExpenseEntry)){
                        $paidExpenses = str_replace(",","",$case->caseExpenseEntry);
                        $totalPaidInvoice -= $paidExpenses;
                    }else{
                        $paidExpenses = $totalPaidInvoice;
                        $totalPaidInvoice -= $paidExpenses;
                    }
                    $totalPaidExpenses += $paidExpenses;
                }   
                if(str_replace(",","",$case->caseAdditionsAdjustment) > 0  && $totalPaidInvoice > 0){
                    if($totalPaidInvoice > str_replace(",","",$case->caseAdditionsAdjustment)){
                        $paidAdditions = str_replace(",","",$case->caseAdditionsAdjustment);
                        $totalPaidInvoice -= $paidAdditions;
                    }else{
                        $paidAdditions = $totalPaidInvoice;
                        $totalPaidInvoice -= $paidAdditions;
                    }
                    $totalPaidAdditions += $paidAdditions;
                }   
                if(str_replace(",","",$case->caseTaxAdjustment) > 0  && $totalPaidInvoice > 0){
                    if($totalPaidInvoice > str_replace(",","",$case->caseTaxAdjustment)){
                        $paidTax = str_replace(",","",$case->caseTaxAdjustment);
                        $totalPaidInvoice -= $paidTax;
                    }else{
                        $paidTax = $totalPaidInvoice;
                        $totalPaidInvoice -= $paidTax;
                    }
                    $totalPaidTax += $paidTax;
                } 
                if(str_replace(",","",$case->caseInterestAdjustment) > 0  && $totalPaidInvoice > 0){
                    if($totalPaidInvoice > str_replace(",","",$case->caseInterestAdjustment)){
                        $paidInterest = str_replace(",","",$case->caseInterestAdjustment);
                        $totalPaidInvoice -= $paidInterest;
                    }else{
                        $paidInterest = $totalPaidInvoice;
                        $totalPaidInvoice -= $paidInterest;
                    }
                    $totalPaidInterest += $paidInterest;
                }  
                if(str_replace(",","",$case->caseDiscountsAdjustment) > 0  && $totalPaidInvoice > 0){
                    if($totalPaidInvoice > str_replace(",","",$case->caseDiscountsAdjustment)){
                        $paidDiscounts = str_replace(",","",$case->caseDiscountsAdjustment);
                        $totalPaidInvoice -= $paidDiscounts;
                    }else{
                        $paidDiscounts = $totalPaidInvoice;
                        $totalPaidInvoice -= $paidDiscounts;
                    }
                    $totalPaidDiscounts += $paidDiscounts;
                }  
                if(str_replace(",","",$case->caseBalanceForwarded) > 0  && $totalPaidInvoice > 0){
                    if($totalPaidInvoice > str_replace(",","",$case->caseBalanceForwarded)){
                        $paidBalanceForward = str_replace(",","",$case->caseBalanceForwarded);
                        $totalPaidInvoice -= $paidBalanceForward;
                    }else{
                        $paidBalanceForward = $totalPaidInvoice;
                        $totalPaidInvoice -= $paidBalanceForward;
                    }
                    $totalPaidBalanceForward += $paidBalanceForward;
                }
                $casesCsvData[]= $case->case_title."|$".number_format($caseFlatfees,2)."|".$caseDuration."|$".number_format($caseTimeEntry,2)."|$".number_format($caseExpenseEntry,2)."|$".number_format($caseBalanceForwarded,2)."|$".number_format($caseInterestAdjustment,2)."|$".number_format($caseTaxAdjustment,2)."|$".number_format($caseAdditionsAdjustment,2)."|$".number_format($caseDiscountsAdjustment,2)."|$0.00|".number_format($caseNonBillableDuration,2)."|$".number_format($caseNonBillableDuration,2)."|".number_format($totalBilled,2)."|$".number_format($paidFlatfee,2)."|$".number_format($paidTimeEntry,2)."|$".number_format($paidExpenses,2)."|$".number_format($paidBalanceForward,2)."|$".number_format($paidInterest,2)."|$".number_format($paidTax,2)."|$".number_format($paidAdditions,2)."|$".number_format($paidDiscounts,2)."|$0.00|$".number_format($case->caseInvoicePaidAmount,2);
            }
        }
        if($export_csv == 1){
            if(count($cases) > 0){
                $casesCsvData[]="Page Total (Sum of the ".count($cases)." rows displayed)|$".number_format($totalCaseFlatfees,2)."|".$totalCaseDuration."|$".number_format($totalCaseTimeEntry,2);
            }
        }
        if($export_csv == 1){
            $file_path =  $folderPath.'/'.$startDt.'_to_'.$endDt.'_case_revenue_reports.csv';  
            $file = fopen($file_path,"w+");
            foreach ($casesCsvData as $exp_data){
            fputcsv($file, explode('|', iconv('UTF-8', 'Windows-1252', $exp_data)));
            }   
            fclose($file); 
            $export_csv_path = asset($fileDestination.'/'.$startDt.'_to_'.$endDt.'_case_revenue_reports.csv');
        }
  
        return view('reports.case_revenue_reports.index', compact("from", "to", "case_status","staff_id", "practice_area", "office", "billing_type", "lead_id", "export_csv_path", "cases", "show_case_with_daterange"));
    }
}
  