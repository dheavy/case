<?php

namespace Mypleasure\Http\Requests;

use Mypleasure\Http\Requests\Request;

class SendInviteRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Check if $user can send invites...
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
            'email' => 'required|email',
            'message' => 'sometimes|between:1,500'
        ];
    }
}
