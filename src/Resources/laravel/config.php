<?php

return [
    'grants' => [
        'refresh_token',
        'password',
        'client_credentials',
        'authorization_code',
        'implicit'
    ],
    'client_credentials' => [
        'access_token_ttl' => new DateInterval('P1Y'),
    ],
    'access_token_ttl' => new DateInterval('PT2H'),
    'refresh_token_ttl' => new DateInterval('P2Y'),
    'private_key' => resource_path('oauth-private.key'),
    'public_key' => resource_path('oauth-public.key'),
];
