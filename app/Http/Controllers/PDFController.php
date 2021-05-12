<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use mikehaertl\wkhtmlto\Pdf;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\CaseIntakeForm,App\IntakeForm,App\IntakeFormFields,App\Countries,App\Firm,App\CaseIntakeFormFieldsData;
class PDFController extends Controller
{
    public function preview()
   {
        return view('chart');
    }
    public function download1()
    {
        $render = view('chart')->render();
        $pdf = new Pdf;
        $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
        $pdf->addPage($render);
        $pdf->setOptions(['javascript-delay' => 10000]);
            //   return $pdf->stream();
        $pdf->saveAs(public_path('intakeform.pdf'));
        return response()->download(public_path('intakeform.pdf'));

    }
    public function download(Request $request)
    {
        $id="9";
        $caseIntakeForm=CaseIntakeForm::where("id",$id)->first();
        $intakeForm=IntakeForm::where("id",$caseIntakeForm['intake_form_id'])->first();
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$caseIntakeForm['intake_form_id'])->orderBy("sort_order","ASC")->get();
        $firmData=Firm::find(Auth::User()->firm_name);
        $country = Countries::get();
        $alreadyFilldedData=CaseIntakeFormFieldsData::where("intake_form_id",$intakeForm->id)->first();
 
        $output= view('lead.details.case_detail.intakeFormPDF',compact('intakeForm','country','firmData','alreadyFilldedData','intakeFormFields'));
        $img="<img src=".asset('images/logo.png').">";
        $filename ='intakeform.pdf';
        $pdf = new Pdf;
        $pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';
        $pdf->addPage($output);
        $pdf->setOptions(['javascript-delay' => 5000]);
        $pdf->setOptions(["footer-right"=> "Page [page] from [topage]"]);
        $pdf->setOptions(["footer-left"=> date('Y-m-d')]);
        // $pdf->setOptions(["header-html"=> '<div>asdas</div>']);
        $pdf->saveAs(public_path($filename));
        return response()->download(public_path($filename));
        exit;
        
        $pdf = PDF::loadView('lead.details.case_detail.intakeFormPDF',array('caseIntakeForm' => $caseIntakeForm,'firmData'=>$firmData,'intakeForm'=>$intakeForm,'intakeFormFields'=>$intakeFormFields,'country'=>$country,'alreadyFilldedData'=>$alreadyFilldedData))->setOptions(['footer-center'=> 'Page [page]']);
        // $pdf->setOptions(['defaultFont' => 'sans-serif']);
        // $pdf->setOptions(['isPhpEnabled' => true]);//->setOptions(['header-html'=> $headerHtml]);
        return $pdf->stream();
    }

    public function downloadIntakeForm(Request $request)
    {
        $id=$request->id;
        $caseIntakeForm=CaseIntakeForm::where("id",$id)->first();
        $intakeForm=IntakeForm::where("id",$caseIntakeForm['intake_form_id'])->first();
        $intakeFormFields=IntakeFormFields::where("intake_form_id",$caseIntakeForm['intake_form_id'])->orderBy("sort_order","ASC")->get();
        $firmData=Firm::find(Auth::User()->firm_name);
        $country = Countries::get();
        $alreadyFilldedData=CaseIntakeFormFieldsData::where("intake_form_id",$intakeForm->id)->first();
 
        return view('lead.details.case_detail.intakeFormPDF',compact('intakeForm','country','firmData','alreadyFilldedData','intakeFormFields'));

        $pdf = PDF::loadView('lead.details.case_detail.intakeFormPDF',array('caseIntakeForm' => $caseIntakeForm,'firmData'=>$firmData,'intakeForm'=>$intakeForm,'intakeFormFields'=>$intakeFormFields,'country'=>$country,'alreadyFilldedData'=>$alreadyFilldedData))->setOptions(['footer-center'=> 'Page [page]']);
        // $pdf->setOptions(['defaultFont' => 'sans-serif']);
        // $pdf->setOptions(['isPhpEnabled' => true]);//->setOptions(['header-html'=> $headerHtml]);
        return $pdf->stream();
    }
}