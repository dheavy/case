<?php

namespace Mypleasure\Http\Requests;

use Mypleasure\Http\Requests\Request;

class StoreUserRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (\Auth::user()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'username'              => 'required|alpha_num|unique:users|between:2,25',
            'email'                 => 'sometimes|required|email|unique:users',
            'password'              => ['required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24', 'confirmed'],
            'password_confirmation' => ['required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24']
        ];
    }
}
