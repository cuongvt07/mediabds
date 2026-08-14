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

        'branding' => [
            'logo' => '',          // absolute URL of the header logo
            'logo_dark' => '',     // optional logo variant for dark backgrounds
            'favicon' => '',       // absolute URL of the favicon (.ico/.png/.svg)
            'tagline' => 'Nền tảng tin đăng bất động sản hàng đầu',
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
            'text' => 'VM24H',
            // style: 'single' (one spot) or 'tiled' (repeated diagonally, unccroppable)
            'style' => 'tiled',
            'angle' => 30,            // tiled diagonal angle in degrees
            'density' => 'sparse',    // tiled spacing: sparse | normal | dense
            // position: top-left, top-right, bottom-left, bottom-right, center (style=single)
            'position' => 'bottom-right',
            'opacity' => 40,          // 0-100 (kept low so the tiled pattern stays subtle)
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

        'seo' => [
            'default_title' => 'BDS Việt — Nền tảng tin đăng bất động sản',
            // %s is replaced by the page title.
            'title_template' => '%s | BDS Việt',
            'default_description' => 'Tìm kiếm và đăng tin cho thuê, mua bán bất động sản: căn hộ, phòng trọ, nhà nguyên căn, đất nền, văn phòng trên toàn quốc.',
            'keywords' => 'bất động sản, nhà đất, cho thuê, mua bán, căn hộ, đất nền',
            'og_image' => '',                 // absolute URL of the default share image
            'robots_index' => true,           // false → noindex the whole site
            'canonical_base' => 'https://vm24h.vn',
            'google_site_verification' => '',
            'facebook_app_id' => '',
            'twitter_handle' => '',           // e.g. @bdsviet
            'analytics_id' => '',             // GA4 (G-XXXX) or GTM (GTM-XXXX)
        ],
    ],
];
