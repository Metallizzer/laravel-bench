<?php

return [
    /*
     * The benchmark page will be available on this path.
     */
    'path' => '/bench',

    /*
     * By default this package will only run in local development.
     * Do not change this, unless you know what your are doing.
     */
    'enabled' => env('APP_ENV') === 'local',

    /*
     * The maximum execution time, in seconds. If set to zero, no time limit is imposed.
     */
    'max_execution_time' => 0,
];
