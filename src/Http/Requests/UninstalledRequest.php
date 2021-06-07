<?php

namespace AtlassianConnectCore\Http\Requests;

/**
 * Class UninstalledRequest
 *
 * @package AtlassianConnectCore\Http\Requests
 */
class UninstalledRequest extends BaseRequest
{
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
        ];
    }
}
