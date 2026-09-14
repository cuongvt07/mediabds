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

    'groq' => [
        'key' => env('GROQ_API_KEY'),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        // [PHASE 2] Smart model routing
        'fast_model' => env('OPENAI_FAST_MODEL', 'gpt-4o-mini'),
        'smart_model' => env('OPENAI_SMART_MODEL', 'gpt-4o'),
        // [STEP C] Intent classifier (pre-flight)
        'classifier_model' => env('OPENAI_CLASSIFIER_MODEL', 'gpt-4o-mini'),
        // [PHASE 4] Embeddings
        'embedding_model' => env('OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
    ],


    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Twilio — gửi OTP SMS cho module Vault (đăng ký/PIN/rút tiền/đổi SĐT).
    // Để trống account_sid/auth_token thì VaultSmsService tự chặn gửi (throw
    // rõ ràng), KHÔNG âm thầm giả vờ thành công — xem VaultSmsService.
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from_number' => env('TWILIO_FROM_NUMBER'), // định dạng E.164, vd +15017122661
    ],

];
