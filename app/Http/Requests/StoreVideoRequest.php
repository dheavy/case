<?php

namespace Mypleasure\Http\Requests;

use Mypleasure\Http\Requests\Request;

class StoreVideoRequest extends Request
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
        return [
            'hash'          => 'required|unique:videos',
            'collection_id' => 'required|numeric',
            'title'         => 'sometimes|between:1,50',
            'original_url'  => 'required|url',
            'embed_url'     => 'required|url',
            'poster'        => 'url',
            'duration'      => 'required|between:8,8',
        ];
    }
}
