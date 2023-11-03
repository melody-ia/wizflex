<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*','admin/*'],

    'allowed_methods' => ['GET','PUT','POST','DELETE','OPTIONS'],

    'allowed_origins' => [
        'https://test.blocksdk.com:1024',
        'https://test.blocksdk.com:1025',
        'https://nft-skin-gigaland-homepage-4.blocksdk.com',
        'https://nft-skin-gigaland-homepage-5.blocksdk.com',
        'https://nft-skin-gigaland-homepage-8.blocksdk.com',
        'https://nft-skin-gigaland-homepage-retro.blocksdk.com',
        'https://nft-test.blocksdk.com',
        'https://test.blocksdk.com',
        'https://demo-admin-nft.blocksdk.com',
	'https://nft-testv3.blocksdk.com',
	'https://wizflex.org',
	'https://www.wizflex.org',
	'https://admin.wizflex.org'
    ],

    /*
     * Patterns that can be used with `preg_match` to match the origin.
     */
    'allowed_origins_patterns' => [],

    /*
     * Sets the Access-Control-Allow-Headers response header. `['*']` allows all headers.
     */
    'allowed_headers' => ['*'],

    /*
     * Sets the Access-Control-Expose-Headers response header with these headers.
     */
    'exposed_headers' => ["*"],

    /*
     * Sets the Access-Control-Max-Age response header when > 0.
     */
    'max_age' => 0,

    /*
     * Sets the Access-Control-Allow-Credentials header.
     */
    'supports_credentials' => true,

];
