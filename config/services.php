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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'unamad_integrations' => [
        'student_url' => env('UNAMAD_STUDENT_API_URL', env('EXTERNAL_API_URL', 'https://daa-documentos.unamad.edu.pe:8081/api/data/student')),
        'teacher_url' => env(
            'UNAMAD_TEACHER_API_URL',
            str_replace('/student', '/teacher', env('EXTERNAL_API_URL', 'https://daa-documentos.unamad.edu.pe:8081/api/data/student'))
        ),
        'student_token' => env('UNAMAD_STUDENT_API_TOKEN', env('EXTERNAL_API_TOKEN')),
        'teacher_token' => env('UNAMAD_TEACHER_API_TOKEN', env('EXTERNAL_API_TOKEN', env('UNAMAD_STUDENT_API_TOKEN'))),
        'verify_ssl' => (bool) env('UNAMAD_API_VERIFY_SSL', env('EXTERNAL_API_VERIFY_SSL', true)),
    ],

];
