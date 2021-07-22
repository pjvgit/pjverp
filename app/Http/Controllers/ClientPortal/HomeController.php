<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;

class HomeController extends Controller 
{
    /**
     * Get client portal dashboard
     */
    public function index()
    {
        return view("client_portal.home");
    }
}