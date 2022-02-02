<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostValidator extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
        return [
            'who' => 'required',
            'title' => 'required',
            'message' => 'required',
            'open_time' => 'required',
            'open_place_name' => 'required',
            'open_place_latitude' => 'required',
            'open_place_longitude' => 'required',
            'public' => 'required',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $res = response()->json(['error' => $validator->messages(),],400);
        throw new HttpResponseException($res);
    }
}
