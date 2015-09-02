<?php

namespace Mypleasure\Http\Requests;

use Mypleasure\Http\Requests\Request;

class DeleteTagRequest extends Request
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
