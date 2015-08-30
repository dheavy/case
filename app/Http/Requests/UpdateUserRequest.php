<?php

namespace Mypleasure\Http\Requests;

use Dingo\Api\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = \Auth::user();
        if ($user && (int) $this->route('id') == (int) $user->id) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     * Email is validated directly in Api/UserController.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'current_password'      => ['sometimes', 'required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24'],
            'password'              => ['sometimes', 'required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24', 'confirmed'],
            'password_confirmation' => ['sometimes', 'required', 'regex:/^[a-zA-Z0-9\s-!]+$/', 'between:6,24']
        ];
    }
}
