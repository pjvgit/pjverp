<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Firm,App\CaseStage,App\CasePracticeArea;
use App\ReferalResource,App\LeadStatus,App\NotHireReasons;
use App\Http\Controllers\CommonController;
use App\CaseEvent,App\IntakeForm,App\IntakeFormFields,App\Countries,App\LeadAdditionalInfo,App\CaseStaff,App\CaseMaster,App\User;
use App\CaseClientSelection,App\EmailTemplate;
use App\IntakeFormDomain,App\CaseIntakeFormFieldsData,App\CaseIntakeForm;
class WithoutloginController extends BaseController
{
    public function __construct()
    {
        // $this->middleware('auth');
    }
    public function formSent(Request $request)
    {
        $formId=$request->id;
        $country = Countries::get();
        $caseIntakeForm=CaseIntakeForm::select("*")->where("form_unique_id",$formId)->first();
        $intakeForm=IntakeForm::where("id",$caseIntakeForm['intake_form_id'])->first();
        $firmData=Firm::find($intakeForm['firm_name']);
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$intakeForm['id'])->orderBy("sort_order","ASC")->get();
        $alreadyFilldedData=CaseIntakeFormFieldsData::where("intake_form_id",$intakeForm['id'])->first();
        return view('intake_forms.formSent',compact('intakeForm','intakeFormFields','firmData','country','alreadyFilldedData','formId'));
    }
}
