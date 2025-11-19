<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auth Service Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for the central authentication microservice.
    |
    */

    'auth_service_url' => env('AUTH_SERVICE_URL', 'http://localhost:8000'),
    'auth_service_api_key' => env('AUTH_SERVICE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The User model to use. You can override this with your own User model
    | that extends Lavalite\Core\Models\User.
    |
    */

    'user_model' => env('LAVALITE_USER_MODEL', \Lavalite\Core\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | Organization Model
    |--------------------------------------------------------------------------
    |
    | The Organization model to use. You can override this with your own
    | Organization model that extends Lavalite\Core\Models\Organization.
    |
    */

    'organization_model' => env('LAVALITE_ORGANIZATION_MODEL', \Lavalite\Core\Models\Organization::class),

    /*
    |--------------------------------------------------------------------------
    | Organization Header
    |--------------------------------------------------------------------------
    |
    | The HTTP header name used to pass organization context.
    |
    */

    'organization_header' => env('LAVALITE_ORGANIZATION_HEADER', 'X-Organization-ID'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTL
    |--------------------------------------------------------------------------
    |
    | Time to live (in seconds) for cached auth service responses.
    |
    */

    'cache_ttl' => [
        'token' => env('LAVALITE_CACHE_TOKEN_TTL', 300),      // 5 minutes
        'user' => env('LAVALITE_CACHE_USER_TTL', 3600),        // 1 hour
        'organization' => env('LAVALITE_CACHE_ORG_TTL', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Enable or disable automatic middleware registration.
    |
    */

    'auto_register_middleware' => env('LAVALITE_AUTO_REGISTER_MIDDLEWARE', true),

    /*
    |--------------------------------------------------------------------------
    | Database Table Prefix
    |--------------------------------------------------------------------------
    |
    | Optional prefix for Lavalite Core database tables.
    |
    */

    'table_prefix' => env('LAVALITE_TABLE_PREFIX', ''),
];
