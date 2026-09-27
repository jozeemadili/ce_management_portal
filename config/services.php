<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],


    /*
     | Beem Africa SMS (https://beem.africa). Credentials live only in .env.
     | SMS_PRETEND=true logs messages instead of sending them (local/testing).
     */
    'beem' => [
        'api_key' => env('SMS_GW_API_KEY'),
        'secret_key' => env('SMS_GW_API_SECRET'),
        'sender_id' => env('SMS_GW_SENDER_ID'),
        'base_url' => env('SMS_GW_BASE_URL', 'https://apisms.beem.africa'),
        'pretend' => env('SMS_PRETEND', false),
        'timeout' => 20,
    ],

];
