<?php

use RalphJSmit\Laravel\SEO\Models\SEO;

return [
    'model' => SEO::class,

    'site_name' => 'Charles R. Bowen',

    'sitemap' => null,

    'canonical_link' => true,

    'robots' => [
        'default' => 'max-snippet:-1,max-image-preview:large,max-video-preview:-1',
        'force_default' => false,
    ],

    'favicon' => 'favicon.ico',

    'title' => [
        'infer_title_from_url' => false,
        'suffix' => ' — Charles R. Bowen',
        'homepage_title' => 'Charles R. Bowen — Product Engineer',
    ],

    'description' => [
        'fallback' => 'Product engineer with 8+ years building full-stack cloud products in Laravel, Vue.js, and TypeScript. Near Bristol, UK.',
    ],

    'image' => [
        'fallback' => 'images/og.png',
    ],

    'author' => [
        'fallback' => 'Charles R. Bowen',
    ],

    'twitter' => [
        '@username' => null,
    ],
];
