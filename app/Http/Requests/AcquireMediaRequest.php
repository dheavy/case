<?php

namespace Mypleasure\Http\Requests;

use Dingo\Api\Http\FormRequest;

class AcquireMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = \JWTAuth::parseToken()->toUser();
        if ($user) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'url' => 'required|url',
            'name' => 'sometimes|between:2,30'
        ];
    }
}
