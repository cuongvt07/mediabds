<?php

return [
    /*
    | Default public site settings. Overridable from the CMS (/website-admin → Cài đặt).
    | Stored as a singleton row in `site_settings` (key = site.settings).
    */
    'defaults' => [
        'contact' => [
            'site_name' => 'BDS Việt',
            'hotline' => '0922 255 544',
            'zalo_phone' => '0922 255 544',
            'email' => 'vmphuthinhland@gmail.com',
            'support_hours' => '8:00 - 21:00 (T2 - CN)',
        ],

        'packages' => [
            'free_daily_quota' => 20,
            'tier_30_price' => 399000,
            'tier_30_quota' => 30,
            'tier_50_price' => 599000,
            'tier_50_quota' => 50,
            'online_payment_enabled' => false,
        ],

        'watermark' => [
            'enabled' => true,
            'text' => 'BDS Việt',
            // position: top-left, top-right, bottom-left, bottom-right, center
            'position' => 'bottom-right',
            'opacity' => 55,          // 0-100
            'font_size' => 22,        // px, scaled to image width
            'color' => '#FFFFFF',
            'margin' => 16,           // px from edge
        ],

        'upload' => [
            'max_size_mb' => 5,       // per image, after client compression
            'max_count' => 20,        // images per listing
            'compress_quality' => 80, // client-side jpeg quality 1-100
            'max_dimension' => 1920,  // client-side longest edge px
        ],
    ],
];
