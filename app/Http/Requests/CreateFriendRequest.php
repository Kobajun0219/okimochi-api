<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use JWTAuth;

class CreateFriendRequest extends FormRequest
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
        $this->user = JWTAuth::parseToken()->authenticate();
        // firstカラムとsecondカラムのユニーク制約のバリデーション。どちらも必須とする
        return [
            'token' => 'required',
            'receive_user_id' => [
                'required',
                Rule::unique('friends')->ignore($this->input('id'))->ignore($this->input('status'))->ignore($this->input('created_at'))->ignore($this->input('updated_at'))->where(function($query) {
                    // 入力されたfirstの値と同じ値を持つレコードでのみ検証する
                    $query->where('request_user_id', $this->user->id,);
                }),
            ],
        ];
    }
}
