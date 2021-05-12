<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request,DateTime;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\CaseEventLocation;
use App\Firm,App\FirmAddress;
use App\FirmEventReminder,App\FirmSolReminder;
class FirmController extends BaseController
{
    public function __construct()
    {
    }
    public function index()
    {
        $id=Auth::user()->id;
        $firmData=Firm::find(Auth::User()->firm_name);
        $user = User::find($id);
        $country = Countries::get();
        $firmAddress = FirmAddress::select("firm_address.*","countries.name as countryname")->leftJoin('countries','firm_address.country',"=","countries.id")->where("firm_address.firm_id",Auth::User()->firm_name)->orderBy('firm_address.is_primary','ASC')->get();
        $FirmEventReminder=FirmEventReminder::where("firm_id",Auth::User()->firm_name)->get();
        $FirmSolReminder=FirmSolReminder::where("firm_id",Auth::User()->firm_name)->get();
        if(!empty($user)){
            return view('firm.index', compact('user','country','firmData','firmAddress','FirmEventReminder','FirmSolReminder'));
        }else{
            return view('pages.404');
        }
    }

    
    public function updateFirm(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'firm_name' => 'required|min:1|max:255|unique:firm,firm_name,'.Auth::User()->firm_name,
        ]);

        if ($validator->fails()) {
        	$errors = $validator->errors();
        	$code = 404;
            $isSuccess = false;
            $request->session()->flash('page', 'infopage');
             return redirect()->back()->withErrors($validator)->withInput();
        }else{
            $firmSave = Firm::find(Auth::User()->firm_name);
            $firmSave->firm_name=trim($request->firm_name);
            $firmSave->save();
            return redirect()->route('firms/setting')->with('success','Firm contact information updated.');
   
        }
    }

    public function addNewFirm(Request $request)
    {
        $country = Countries::get();
        return view('firm.addFirm',compact('country'));
    }
    public function saveNewFirm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'office_name' => 'required|max:255',
            'main_phone' => 'nullable|numeric',
            'fax_line' => 'nullable|numeric'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $FirmAddress=new FirmAddress;
            $FirmAddress->office_name=$request->office_name; 
            $FirmAddress->main_phone=$request->main_phone; 
            $FirmAddress->fax_line=$request->fax_line; 
            $FirmAddress->address=$request->address; 
            $FirmAddress->apt_unit=$request->apt_unit; 
            $FirmAddress->city=$request->city; 
            $FirmAddress->state=$request->state; 
            $FirmAddress->post_code=$request->post_code; 
            $FirmAddress->country=$request->country; 
            $FirmAddress->firm_id=Auth::User()->firm_name;
            $FirmAddress->save();
          
            if(isset($request->primary_office) && $request->primary_office=="on"){
                FirmAddress::where('firm_id',Auth::User()->firm_name)->update(['is_primary'=>'no']);
                $FirmAddress->is_primary="yes";
                $FirmAddress->save();
            }
            session(['popup_success' => 'Office information added.']);

            return response()->json(['errors'=>'','id'=>$FirmAddress->id]);
            exit;
        }
    }
    public function editFirm(Request $request)
    {
        $FirmAddress=FirmAddress::find($request->office_id);
        $country = Countries::get();
        return view('firm.editFirm',compact('country','FirmAddress'));
    }
    public function UpdateNewFirm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'office_name' => 'required|max:255',
            'main_phone' => 'nullable|numeric',
            'fax_line' => 'nullable|numeric'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $FirmAddress=FirmAddress::find($request->id);
            $FirmAddress->office_name=$request->office_name; 
            $FirmAddress->main_phone=$request->main_phone; 
            $FirmAddress->fax_line=$request->fax_line; 
            $FirmAddress->address=$request->address; 
            $FirmAddress->apt_unit=$request->apt_unit; 
            $FirmAddress->city=$request->city; 
            $FirmAddress->state=$request->state; 
            $FirmAddress->post_code=$request->post_code; 
            $FirmAddress->country=$request->country; 
            $FirmAddress->firm_id=Auth::User()->firm_name;
            $FirmAddress->save();


            if(isset($request->primary_office) && $request->primary_office=="on"){
                FirmAddress::where('firm_id',Auth::User()->firm_name)->update(['is_primary'=>'no']);
                $FirmAddress->is_primary="yes";
                $FirmAddress->save();
            }
            session(['popup_success' => 'Office information updated.']);

            return response()->json(['errors'=>'','id'=>$FirmAddress->id]);
            exit;
        }
    }
    public function deleteFirm(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'firm_id' => 'required|numeric'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            FirmAddress::where('id',$request->firm_id)->delete();
            session(['popup_success' => ' Office deleted']);
            return response()->json(['errors'=>'','id'=>$request->firm_id]);
            exit;
        }
        
    }

    public function editPreferance(Request $request)
    {
        // print_r($request->all());exit;
        $validator = \Validator::make($request->all(), [
            'firm_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $FirmAddress=Firm::find(Auth::User()->firm_name);
            if(isset($request->client_portal_access) && $request->client_portal_access=="on"){
                $FirmAddress->client_portal_access="yes"; 
                $FirmAddress->save();
            }else{
                $FirmAddress->client_portal_access="no"; 
                $FirmAddress->save();
            }

            if(isset($request->statute_of_limitations) && $request->statute_of_limitations=="on"){
                $FirmAddress->sol="yes";
                $FirmAddress->save(); 
            }

            // if ($request->hasFile('firm_logo')) {
            //     $image = $request->file('firm_logo');
            //     $name = "firm_".$FirmAddress->id."_".time().'.'.$image->getClientOriginalExtension();
            //     $destinationPath = public_path('/upload/firm/');
            //     $image->move($destinationPath, $name);
            //     $FirmAddress->firm_logo= $name;
            // }

            if(isset($request->imageRemoveFromFirm) && $request->imageRemoveFromFirm=="yes"){
                $filePath=public_path('/upload/firm/'.$FirmAddress->firm_logo);
                if(file_exists($filePath) && $FirmAddress->firm_logo != NULL){
                    unlink($filePath);
                }
                $FirmAddress->firm_logo= NULL;
                $FirmAddress->save();
            }
            if ($request->hasFile('firm_logo')) {
                $filePath=public_path('/upload/firm/'.$FirmAddress->firm_logo);
                if(file_exists($filePath) && $FirmAddress->firm_logo != NULL){
                    unlink($filePath);
                }
                $image = $request->file('firm_logo');
                $input['imagename'] = "firm_".$FirmAddress->id."_".time().'.'.$image->getClientOriginalExtension();
                $destinationPath = public_path('/upload/firm/');
                $img = Image::make($image->path());
                $img->resize(200, 50)->save($destinationPath.'/'.$input['imagename']);
                $FirmAddress->firm_logo= $input['imagename'];
                $FirmAddress->save();
            }

            $this->saveEventReminder($request);
            $this->saveSolReminder($request);
            return redirect()->route('firms/setting')->with('success','Firm preferences updated.');
            exit;
        }
    }

    public function saveEventReminder($request)
    {
        FirmEventReminder::where("firm_id",Auth::User()->firm_name)->delete();

       for($i=0;$i<count($request['reminder_user_type'])-1;$i++){
           $CaseEventReminder = new FirmEventReminder;
           $CaseEventReminder->firm_id=Auth::User()->firm_name; 
           $CaseEventReminder->reminder_type=$request['reminder_type'][$i];
           $CaseEventReminder->reminer_number=$request['reminder_number'][$i];
           $CaseEventReminder->reminder_frequncy=$request['reminder_time_unit'][$i];
           $CaseEventReminder->reminder_user_type=$request['reminder_user_type'][$i];
           $CaseEventReminder->created_by=Auth::user()->id; 
           $CaseEventReminder->save();
       }
   }
   public function saveSolReminder($request)
    {
        FirmSolReminder::where("firm_id",Auth::User()->firm_name)->delete();
        for($i=0;$i<count($request['sol_reminder_type'])-1;$i++){
            $CaseSolReminder = new FirmSolReminder;
            $CaseSolReminder->firm_id=Auth::User()->firm_name; 
            $CaseSolReminder->reminder_type=$request['sol_reminder_type'][$i]; 
            $CaseSolReminder->reminer_days=$request['sol_reminder_number'][$i];
            $CaseSolReminder->created_by=Auth::User()->id; 
            $CaseSolReminder->save();
        }
    
   }
}
  
