<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller {

    /**
     * Get admin dashboard
     */
    public function index()
    {
        return view('admin_panel.dashboard');
    }
}