<?php

return [
    'allowed_hosts' => array_filter(array_map(
        'trim',
        explode(',', (string) env('SECURITY_ALLOWED_HOSTS', 'coldpay.in,.coldpay.in,127.0.0.1,localhost'))
    )),

    'blocked_extensions' => [
        '.bak',
        '.backup',
        '.conf',
        '.config',
        '.env',
        '.ini',
        '.log',
        '.old',
        '.orig',
        '.php~',
        '.sql',
        '.sqlite',
        '.swp',
        '.zip',
    ],

    'sensitive_path_patterns' => [
        '#(^|/)\.#',
        '#(^|/)(app|bootstrap|config|database|resources|routes|storage|vendor)(/|$)#i',
        '#(^|/)(composer\.(json|lock)|package(-lock)?\.json|yarn\.lock|vite\.config\.js)$#i',
        '#(^|/)(artisan|phpunit\.xml|server\.php)$#i',
        '#\.\./#',
        '#%2e%2e#i',
    ],
];
