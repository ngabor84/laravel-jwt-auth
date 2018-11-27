<?php

return [
    'secret' => env('JWT_SECRET'),
    'algo' => 'HS256',
    'expiration' => 10, // 10 minutes
];
