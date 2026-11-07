<?php

return [
    'app_id' => env('JAAS_APP_ID'),
    'key_id' => env('JAAS_KEY_ID'),
    'private_key_path' => env('JAAS_PRIVATE_KEY_PATH', 'storage/app/private/jaas-private.pk'),

    // Optional: the key's contents, for hosts where you cannot upload a file.
    'private_key' => env('JAAS_PRIVATE_KEY'),
];