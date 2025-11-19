<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Social Login Providers
    |--------------------------------------------------------------------------
    |
    | Configure which social login providers are enabled and their settings.
    | Each provider can be individually enabled/disabled and customized.
    |
    */

    'providers' => [
        'google' => [
            'enabled' => env('GOOGLE_LOGIN_ENABLED', true),
            'label' => 'Google',
            'icon' => 'google', // Icon identifier for frontend
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('GOOGLE_REDIRECT_URL', env('APP_URL').'/auth/google/callback'),
        ],

        'microsoft' => [
            'enabled' => env('MICROSOFT_LOGIN_ENABLED', true),
            'label' => 'Microsoft',
            'icon' => 'microsoft',
            'client_id' => env('MICROSOFT_CLIENT_ID'),
            'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
            'redirect' => env('MICROSOFT_REDIRECT_URL', env('APP_URL').'/auth/microsoft/callback'),
        ],

        // Add more providers as needed:
        // 'github' => [
        //     'enabled' => env('GITHUB_LOGIN_ENABLED', false),
        //     'label' => 'GitHub',
        //     'icon' => 'github',
        //     'client_id' => env('GITHUB_CLIENT_ID'),
        //     'client_secret' => env('GITHUB_CLIENT_SECRET'),
        //     'redirect' => env('GITHUB_REDIRECT_URL', env('APP_URL') . '/auth/github/callback'),
        // ],
        //
        // 'facebook' => [
        //     'enabled' => env('FACEBOOK_LOGIN_ENABLED', false),
        //     'label' => 'Facebook',
        //     'icon' => 'facebook',
        //     'client_id' => env('FACEBOOK_CLIENT_ID'),
        //     'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        //     'redirect' => env('FACEBOOK_REDIRECT_URL', env('APP_URL') . '/auth/facebook/callback'),
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-verify Email
    |--------------------------------------------------------------------------
    |
    | Automatically mark emails as verified when users sign up via social login.
    | Most social providers have already verified the email address.
    |
    */

    'auto_verify_email' => env('SOCIAL_LOGIN_AUTO_VERIFY_EMAIL', true),

    /*
    |--------------------------------------------------------------------------
    | Allow Account Linking
    |--------------------------------------------------------------------------
    |
    | Allow users to link multiple social accounts to the same email address.
    | If disabled, each social account will create a separate user.
    |
    */

    'allow_account_linking' => env('SOCIAL_LOGIN_ALLOW_LINKING', true),

];
