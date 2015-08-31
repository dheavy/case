<?php

namespace Mypleasure\Http\Requests;

use Dingo\Api\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
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
        } else {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // The private field will be set to false by default unless spcified.
        return [
            'name' => 'required|between:2,30',
            'slug' => 'sometimes|between:2,30'
        ];
    }
}
