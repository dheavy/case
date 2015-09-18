<?php

namespace Mypleasure\Http\Requests;

use Dingo\Api\Http\FormRequest;

class CreateUserFromInviteRequest extends FormRequest
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
            'email'                 => 'required|email',
            'code'                  => 'required|min:5',
            'username'              => 'required|alpha_num|unique:users|between:2,25',
            'email'                 => 'sometimes|required|email|unique:users',
            'password'              => ['required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24', 'confirmed'],
            'password_confirmation' => ['required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24']
        ];
    }
}
