<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Atlassian Connect Add-On Settings
     |--------------------------------------------------------------------------
     |
     | The name of the add-on you can see in the add-ons installation page
     |
     */

    'name' => env('PLUGIN_NAME', 'Atlassian Connect Add-on'),

    /*
     |--------------------------------------------------------------------------
     | Add-on key
     |--------------------------------------------------------------------------
     |
     | The key of the add-on. Should be simple, unique and accordant with name
     |
     */

    'key' => env('PLUGIN_KEY', 'sample-plugin'),

    /*
     |--------------------------------------------------------------------------
     | Add-on base URL
     |--------------------------------------------------------------------------
     |
     | The base URL of the add-on. Atlassian will send requests to the following
     | host
     |
     */

    'url' => env('PLUGIN_URL', env('APP_URL', 'http://localhost')),

    /*
     |--------------------------------------------------------------------------
     | Add-On description
     |--------------------------------------------------------------------------
     |
     | The description of the add-on. Mostly displayed with the name
     |
     */

    'description' => env('PLUGIN_NAME', 'An example add-on description'),

    /*
     |--------------------------------------------------------------------------
     | Vendor
     |--------------------------------------------------------------------------
     |
     | It's all about the add-on's vendor
     |
     */

    'vendor' => [
        'name' => env('PLUGIN_VENDOR_NAME', 'Laravel community'),
        'url' => env('PLUGIN_VENDOR_URL', '/'),
    ],

    /*
     |--------------------------------------------------------------------------
     | Add-On Version
     |--------------------------------------------------------------------------
     |
     | Version of the add-on
     |
     */

    'version' => env('PLUGIN_VERSION', '1.0.0'),

    /*
     |--------------------------------------------------------------------------
     | Authentication Type
     |--------------------------------------------------------------------------
     |
     | The type of authentication. Possible values: JWT, jwt, NONE, none
     |
     */

    'authType' => env('PLUGIN_AUTH_TYPE', 'jwt'),

    /*
     |--------------------------------------------------------------------------
     | The name of the Tenant table
     |--------------------------------------------------------------------------
     |
     | This value affects to package migrations, console commands and queries
     |
     */

    'tenant' => 'tenant',

    /*
     |--------------------------------------------------------------------------
     | Whether base plugin routes should be loaded
     |--------------------------------------------------------------------------
     |
     | If you want to disable core routes you should set value to false
     |
     */

    'loadRoutes' => true,

    /*
     |--------------------------------------------------------------------------
     | Whether need to enable safe deletion of the Tenant table
     |--------------------------------------------------------------------------
     |
     | This value controls keeping tenant entity when add-on uninstalling
     |
     */

    'safeDelete' => true,

    /*
     |--------------------------------------------------------------------------
     | The webhook listeners
     |--------------------------------------------------------------------------
     |
     | You can define here listeners of the webhook events
     |
     */
    'webhooks' => [],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guard Alias
    |--------------------------------------------------------------------------
    |
    | Override authentication guard alias.
    |
    */
    'guard' => 'jwt'
];
