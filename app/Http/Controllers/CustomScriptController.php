<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request,DateTime;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\ContractUserCase,App\CaseMaster,App\ContractUserPermission,App\ContractAccessPermission;
use App\DeactivatedUser,App\TempUserSelection,App\CasePracticeArea,App\CaseStage,App\CaseClientSelection;
use App\CaseStaff,App\CaseUpdate,App\CaseStageUpdate,App\CaseActivity;
use App\CaseEvent,App\CaseEventLocation,App\EventType;
use Carbon\Carbon,App\CaseEventReminder,App\CaseEventLinkedStaff;
use App\Http\Controllers\CommonController,App\CaseSolReminder;
use DateInterval,DatePeriod,App\CaseEventComment;
use App\Task,App\CaseTaskReminder,App\CaseTaskLinkedStaff,App\TaskChecklist;
use App\TaskReminder,App\TaskActivity,App\TaskTimeEntry,App\TaskComment;
use App\TaskHistory,App\LeadAdditionalInfo,App\Invoices;
use Illuminate\Support\Str;
class CustomScriptController extends BaseController
{
    public function __construct()
    {
       
    }
    public function assignTokenToInvoice()
    {
        //For token generate Code
        $Invoices=Invoices::get();
        foreach($Invoices as $k){
           DB::table('invoices')->where("id",$k->id)->update([
                'invoice_token'=>Str::random(250)
            ]);
        }
        echo "Done";
    }

}
  
