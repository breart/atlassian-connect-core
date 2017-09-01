<?php

namespace AtlassianConnectCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UninstalledRequest
 *
 * @package AtlassianConnectCore\Http\Requests
 */
class UninstalledRequest extends FormRequest
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
            'user_key' => 'required'
        ];
    }
}
