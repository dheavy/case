<?php

namespace Mypleasure\Http\Requests;

use Dingo\Api\Http\FormRequest;
use Mypleasure\Collection;

class UpdateCollectionRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = \JWTAuth::parseToken()->toUser();
        $collection = Collection::find((int) $this->route('id'));

        if ($user && ((int) $collection->user_id === (int) $user->id) || $user->admin) {
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
            'name' => 'sometimes|required|between:2,30',
            'slug' => 'sometimes|between:2,30',
            'private' => 'sometimes|required|boolean'
        ];
    }
}
