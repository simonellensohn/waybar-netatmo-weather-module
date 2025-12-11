<?php

return [

    'default' => 'local',

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => env('HOME').'/.local/state/waybar-netatmo-weather-module',
        ],
    ],

];
