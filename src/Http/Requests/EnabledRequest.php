<?php

namespace AtlassianConnectCore\Http\Requests;

/**
 * Class EnabledRequest
 *
 * @package AtlassianConnectCore\Http\Requests
 */
class EnabledRequest extends BaseRequest
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
            'user_id' => 'required',
            'user_key' => 'required',
        ];
    }
}
