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
                    $casesCsvData[]=$v->invoice_id."|".$v->contact_name."|".$v->ctitle."|".$v->total_amount_new."|".$v->total_amount_new."|".$v->due_amount_new."|".(($v->due_date!=NULL)? $v->due_date : '--')."|".$v->status."|".$v->days_aging;
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
    
    public function caseRevenueReportsView(){
        return view('reports.case_revenue_reports.index');
    }
}
  