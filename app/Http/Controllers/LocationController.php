<?php

namespace App\Http\Controllers;
use App\User,App\EmailTemplate,App\Countries;
use Illuminate\Http\Request,DateTime;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\CaseEventLocation,App\CaseEvent;
class LocationController extends BaseController
{
    public function __construct()
    {
       
    }
    public function index()
    {
         return view('location.index');
    }

    public function loadLocation()
    {   

        $columns = array('id', 'location_name');
        $requestData= $_REQUEST;
        
        $CaseEventLocation = CaseEventLocation::leftJoin("users","case_event_location.created_by","=","users.id")->leftJoin('countries','case_event_location.country',"=","countries.id")->select(DB::raw('CONCAT_WS(",",address1,address2,case_event_location.city,case_event_location.state,name) as map_address'),'case_event_location.*',DB::raw('CONCAT_WS(" ",users.first_name,users.last_name) as created_by_name'),"users.id as uid",'countries.name');
        $CaseEventLocation=$CaseEventLocation->where("location_future_use","yes");
        $totalData=$CaseEventLocation->count();
        $totalFiltered = $totalData; 
        if( !empty($requestData['search']['value']) ) {   
            $CaseEventLocation = $CaseEventLocation->where( function($q) use ($requestData){
                $q->where( function($select) use ($requestData){
                    $select->orWhere( DB::raw('CONCAT(address1, " ", address2)'), 'like', "%".$requestData['search']['value']."%");
                    $select->orWhere('location_name ', 'like', "%".$requestData['search']['value']."%" );
                });
            });
        }
        if( !empty($requestData['search']['value']) ) { 
            $totalFiltered = $CaseEventLocation->count(); 
        }
        $CaseEventLocation = $CaseEventLocation->offset($requestData['start'])->limit($requestData['length']);
        $CaseEventLocation = $CaseEventLocation->orderBy($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
        $CaseEventLocation = $CaseEventLocation->get();
        $json_data = array(
            "draw"            => intval( $requestData['draw'] ),   
            "recordsTotal"    => intval( $totalData ),  
            "recordsFiltered" => intval( $totalFiltered ), 
            "data"            => $CaseEventLocation 
        );
        echo json_encode($json_data);  
    }

    public function loadAddLocationPopup()
    {
        $country = Countries::get();
        return view('location.loadAddLocationPopup',compact('country'));
    }
    public function saveAddLocationPopup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'location_name' => 'required|max:255'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CaseEventLocation=new CaseEventLocation;
            $CaseEventLocation->location_name=$request->location_name; 
            $CaseEventLocation->address1=$request->address1;
            $CaseEventLocation->address2=$request->address2;
            $CaseEventLocation->city=$request->city;
            $CaseEventLocation->state=$request->state;
            $CaseEventLocation->postal_code=$request->zip;
            $CaseEventLocation->country=$request->country;
            $CaseEventLocation->location_future_use='yes';
            $CaseEventLocation->created_by =Auth::User()->id;
            $CaseEventLocation->save();
            return response()->json(['errors'=>'','id'=>$CaseEventLocation->id]);
            exit;
        }
    }

    public function loadEditLocationPopup(Request $request)
    {
        $country = Countries::get();
        $CaseEventLocation=CaseEventLocation::find($request->id);
        return view('location.loadEditLocationPopup',compact('country','CaseEventLocation'));
    }
    public function saveEditLocationPopup(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'location_name' => 'required|max:255'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            $CaseEventLocation=CaseEventLocation::find($request->id);
            $CaseEventLocation->location_name=$request->location_name; 
            $CaseEventLocation->address1=$request->address1;
            $CaseEventLocation->address2=$request->address2;
            $CaseEventLocation->city=$request->city;
            $CaseEventLocation->state=$request->state;
            $CaseEventLocation->postal_code=$request->zip;
            $CaseEventLocation->country=$request->country;
            $CaseEventLocation->updated_by =Auth::User()->id;
            $CaseEventLocation->save();
            return response()->json(['errors'=>'','id'=>$CaseEventLocation->id]);
            exit;
        }
    }

    public function deleteLocation(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'location_id' => 'required'
        ]);
        if($validator->fails())
        {
            return response()->json(['errors'=>$validator->errors()->all()]);
        }else{
            CaseEvent::where('event_location_id',$request->location_id)->update(['event_location_id'=>NULL]);
            CaseEventLocation::where('id',$request->location_id)->delete();
            return response()->json(['errors'=>'','id'=>$request->location_id]);
            exit;
        }
        
    }
}
  
