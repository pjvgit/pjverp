<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        "/login",
        "client/bills/conekta/webhook"
    ];
    
    public function render($request, Exception $e)
    {

        if ($e instanceof \Illuminate\Session\TokenMismatchException)
        {
            return redirect()
                    ->back()
                    ->withInput($request->except('password'))
                    ->with([
                        'message' => 'Validation Token was expired. Please try again',
                        'message-type' => 'danger']);
        }   

        return parent::render($request, $e);
    }
}
