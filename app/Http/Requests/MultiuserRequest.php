<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MultiuserRequest extends FormRequest
{
    public function authorize()
{
    return true;
}

/**
 * Get the validation rules that apply to the request.
 *
 * @return array
 */
public function rules()
{ 
    $rules = [
        'first_name' => 'required|max:255',
      ];
      foreach($this->request->get('first_name') as $key => $val)
      {
            $rules['first_name.'.$key] = 'required|max:10';
      }
      return $rules;
}

public function messages() 
{
    $messages = [];
    foreach($this->request->get('first_name') as $key => $val)
    {
      $messages['first_name.'.$key.'.max'] = 'The field labeled "Book Title '.$key.'" must be less than :max characters.';
    }
    return $messages;
}

}
