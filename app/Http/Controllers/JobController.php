<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmailsâ€‹;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function store(Request $request)
    {
        // Create post here ..
        SendEmailsâ€‹::dispatch($request);
    }
}
