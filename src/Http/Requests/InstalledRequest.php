<?php

namespace AtlassianConnectCore\Http\Requests;

/**
 * Class InstalledRequest
 *
 * @package AtlassianConnectCore\Http\Requests
 */
class InstalledRequest extends BaseRequest
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
            'oauthClientId' => 'string',
            'sharedSecret' => 'required',
            'serverVersion' => 'required',
            'pluginsVersion' => 'required',
            'baseUrl' => 'required',
            'productType' => 'required',
            'description' => 'required',
            'eventType' => 'required'
        ];
    }
}
