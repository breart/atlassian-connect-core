<?php

namespace AtlassianConnectCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class EnabledRequest
 *
 * @package AtlassianConnectCore\Http\Requests
 */
class EnabledRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'key' => 'required',
            'clientKey' => 'required',
            'serverVersion' => 'required',
            'pluginsVersion' => 'required',
            'baseUrl' => 'required',
            'productType' => 'required',
            'description' => 'required',
            'eventType' => 'required',
            'user_id' => 'required',
            'user_key' => 'required',
        ];
    }
}
