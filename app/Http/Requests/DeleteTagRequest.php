<?php

namespace Mypleasure\Http\Requests;

use use Dingo\Api\Http\FormRequest;

class DeleteTagRequest extends FormRequest
{
    /**
     * Only admin may delete a tag.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = \JWTAuth::parseToken()->toUser();
        if ($user && $user->admin === true) {
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
            //
        ];
    }
}
