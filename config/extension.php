<?php

return [
    'signature_ttl_seconds' => 300,

    'defaults' => [
        'enabled' => true,
        'minVersion' => '1.0.0',
        'pollIntervalSeconds' => 300,
        'maintenanceMessage' => null,
        'branding' => [
            'title' => 'DEV CƯỜNG TOOL',
            'supportPhone' => '0943206425',
            'supportUrl' => null,
        ],
        'features' => [
            'uiEnabled' => true,
            'autoNavigation' => false,
        ],
        'courses' => [
            ['path' => '/slides/mon-hoc-phap-luat-giao-thong-uong-bo-2025-347', 'label' => 'Pháp luật giao thông đường bộ', 'enabled' => true, 'priority' => 1],
            ['path' => '/slides/mon-hoc-ao-uc-nguoi-lai-xe-o-to-2025-351', 'label' => 'Đạo đức người lái xe', 'enabled' => true, 'priority' => 2],
            ['path' => '/slides/mon-hoc-ki-thuat-lai-xe-o-to-350', 'label' => 'Kĩ thuật lái xe', 'enabled' => true, 'priority' => 3],
            ['path' => '/slides/cau-tao-va-sua-chua-thong-thuong-xe-oto-346', 'label' => 'Cấu tạo và sửa chữa', 'enabled' => true, 'priority' => 4],
            ['path' => '/slides/khoa-hoc-mo-phong-349', 'label' => 'Mô phỏng tình huống giao thông', 'enabled' => true, 'priority' => 5],
        ],
    ],
];
