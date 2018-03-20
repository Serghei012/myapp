<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get(
    '/',
    [
        'uses' => 'IndexController@getWelcomeJson'
    ]
);

Route::get(
    '/redis-health-check',
    [
        'uses' => 'HealthCheckController@getRedisStatus'
    ]
);

Route::get(
    'readyz',
    function () {
        return response()->json(
            [
                'ok'
            ],
            200
        );
    }
);

Route::get(
    'healthz',
    function () {
        return response()->json(
            [
                'ok'
            ],
            200
        );
    }
);

Route::get(
    'sys-env',
    function () {
        return response()->json(
            $_SERVER,
            200
        );
    }
);
Route::get(
    'status',
    [
        'uses' => 'HealthCheckController@getStatus'
    ]
);

Route::get(
    'logging',
    [
        'uses' => 'HealthCheckController@logTest'
    ]
);
