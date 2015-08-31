<?php

namespace Mypleasure\Http\Requests;

use Dingo\Api\Http\FormRequest;
use Mypleasure\Collection;

class DeleteCollectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = \JWTAuth::parseToken()->toUser();
        $collection = Collection::find($this->route('id'));

        // No user.
        if (!$user) {
            return false;
        }

        // No collection.
        if (!$collection) {
            return false;
        }

        // User and collection, but either not belonging to user,
        // and user is not an admin.
        if ((int) $user->id !== (int) $collection->user_id && !$user->admin) {
            return false;
        }

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
            //
        ];
    }
}
