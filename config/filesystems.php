<?php

return [
    'default' => 'local',

    'local' => [
        'driver' => 'local',
        'root' => getcwd(),
    ],

    'test' => [
        'driver' => 'local',
        'root' => storage_path(),
    ],
];
