<?php

namespace Mypleasure\Http\Requests;

use Mypleasure\Http\Requests\Request;
use Mypleasure\Video;

class DeleteVideoRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = \JWTAuth::parseToken()->toUser();
        $video = Video::find((int) $this->route('id'));

        if (!$user) {
            return false;
        }

        if ((int) $video->getOwner()->id === (int) $user->id) {
            return true;
        }

        if ($user->admin) {
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
            //
        ];
    }
}
