<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmails​;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function store(Request $request)
    {
        // Create post here ..
        // SendEmails​::dispatch($request); // Commented coz not in use
    }
}
