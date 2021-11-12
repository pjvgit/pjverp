<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule as ValidationRule;

class UniqueEmail implements Rule
{
    protected $userId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $userId = $this->userId;
        Log::info("unique email user id out: ".$userId);
        $validator = Validator::make([$attribute], [
            'email' => ['nullable', ValidationRule::unique('users')->where(function($query) use($userId){
                // Log::info("unique email user id: ".$userId);
                $query->where('firm_name', auth()->user()->firm_name);
                // if($userId) {
                //     $query->where('id', '!=', $userId);
                // }
            })/* ->ignore($this->userId) */],
        ]);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The email has already been taken.';
    }
}
