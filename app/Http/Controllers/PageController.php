<?php

namespace App\Http\Controllers;

use App\User,App\EmailTemplate,App\PlanHistory;
use Illuminate\Http\Request;
use DB,Validator,Session,Mail,Storage,Image;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class PageController extends BaseController
{
    public function __construct()
    {
        // $this->middleware("auth");
    }
    
    public function index()
    {
        return view('pages.home');
    }
    public function terms()
    {
        return view('pages.terms');
    }
    public function privacy()
    {
        return view('pages.privacy');
    }  
    public function supportPage()
    {
        return view('pages.support_page');
    }  
}
